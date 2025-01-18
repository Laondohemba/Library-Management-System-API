<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
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
