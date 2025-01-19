<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Book;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\BooksBorrowing;

class BookController extends Controller
{
    /**
     * Display a listing of the resource for admin.
     */
    public function index()
    {
        $books = Book::paginate(10);

        if(!$books)
        {
            return response()->json([
                'message' => 'No books found',
            ]);
        }

        return response()->json([
            'books' => $books,
        ]);
    }

    //handle due date
    public function handleDate($date)
    {

        // Convert to Carbon instance
        $carbonDate = Carbon::parse($date);

        // Set the current time dynamically
        $currentTime = Carbon::now();
        $carbonDate->setTime(
            $currentTime->hour,
            $currentTime->minute,
            $currentTime->second
        );

        // Convert to MySQL-compatible datetime format
        $mysqlTimestamp = $carbonDate->format('Y-m-d H:i:s');

        return $mysqlTimestamp; 

    }

    //book borrowing by user
    public function borrowBook(Request $request, Book $book)
    {
        try {
            $validated = $request->validate([
                'borrowed_at' => ['date'],
                'due_date' => ['required', 'date']
            ]);
    
            $token = JWTAuth::getToken();
            $user = JWTAuth::authenticate($token);
            $data = [
                'user_id' => $user->id,
                'book_id' => $book->id,
                'borrowed_at' => $this->handleDate($validated['borrowed_at']) ?? now(),
                'due_date' => $this->handleDate($validated['due_date']),
            ];

            if($book->copies_available < 1)
            {
                return response()->json([
                    'errors' => 'Book not available. Try again later.'
                ], 400);
            }

            if(BooksBorrowing::create($data))
            {
                $book->decrement('copies_available');

                return response()->json([
                    'message' => 'Book borrowed successfully',
                ], 201);
            }

            return response()->json([
                'error' => 'Sorry. Book borrowing failed',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            
            return response()->json([
                'errors' => $e->errors(),
            ]);
        }
    }

    //return borrowed book
    public function returnBook($id)
    {
        
        $returnedAt = now();
        $booksBorrowing = BooksBorrowing::find($id);
        $bookId = $booksBorrowing->book_id;
        $book = Book::find($bookId);

        if($booksBorrowing->returned_at != null)
        {
            return response()->json([
                'message' => 'This book has already been returned',
            ]);
        }

        if($returnedAt->gt($booksBorrowing->due_date)){
            $daysLate = $returnedAt->diffInDays($booksBorrowing->due_date);
            $fine = $daysLate * 10;

            $booksBorrowing->update([
                'returned_at' => $returnedAt,
                'fine' => $fine
            ]);

            $book->increment('copies_available');


            return response()->json([
                'message' => 'Book returned successfully with a fine of ' .$fine,
            ]);
        }

        $booksBorrowing->update([
            'returned_at' => $returnedAt,
        ]);

        $book->increment('copies_available');

        return response()->json([
            'message' => 'Book returned with no fine',
            // 'message' => $booksBorrowing,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        try{
            $validated = $request->validate([
                'author' => ['required', 'max:255'],
                'title' => ['required', 'max:255'],
                'copies_available' => ['required', 'numeric'],
            ]);

            $book = Book::create($validated);

            if($book)
            {
                return response()->json([
                    'message' => 'Book created successfully',
                    'book' => $book,
                ]);
            }

            return response()->json([
                'errors' => 'Sorry. An error occured while adding the book!'
            ]);

        }catch(\Illuminate\Validation\ValidationException $e){

            return response()->json([
                'errors' => $e->errors(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        try{
            $validated = $request->validate([
                'author' => ['required', 'max:255'],
                'title' => ['required', 'max:255'],
                'copies_available' => ['required', 'numeric'],
            ]);

            if($book->update($validated))
            {
                return response()->json([
                    'message' => 'Book updated successfully',
                    'book' => $book,
                ]);
            }

            return response()->json([
                'errors' => 'Sorry. An error occured while updating the book!'
            ]);

        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){

            return response()->json([
                'errors' => 'Book not found!',
            ]);
        }catch(\Illuminate\Validation\ValidationException $e){

            return response()->json([
                'errors' => $e->errors(),
            ]);
        }catch(\Exception $e){

            return response()->json([
                'errors' => 'An unexpected error occured',
            ]);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        if($book->delete())
        {
            return response()->json([
                'message' => 'Book deleted successfully'
            ]);
        }
    }
}
