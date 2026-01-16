<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse ;

trait ApiResponse
{
    /**
     * Success Response
     */
    protected function successResponse($data = null ,string $message = 'Success' , int $statusCode = 200) : JsonResponse
    {
        return response()->json([
            'success'   => true ,
            'message'   => $message ,
            'data'      => $data ,
            'timestamp' => now()->toIso8601String(),
        ] , $statusCode);
    }

    /**
     * Error Response
     */

    protected function errorResponse(string $message , int $statusCode = 400 , $errors = null)
    {
        $response = [
            'success' => false , 
            'message' => $message ,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }
        return response()->json($response , $statusCode);
    }

    /**
     * paginated response 
     */

    protected function paginatedResponse($paginator , string $message = 'Success')
    {
        return response()->json([
            'success' => true ,
            'message' => $message ,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage() ,
                'last_page'    => $paginator->lastPage() ,
                'per_page'     => $paginator->perPage() ,
                'total'        => $paginator->total() ,
                'form'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(), 
            ],
            'timestamp' => now()->toIso8601String(),
        ] , 200);
    }

    /**
     * Resource created response 
     */

    protected function createResponse($data , string $message = 'Resource Created Successfully')
    {
        return $this->successResponse($data , $message,201);
    }

    /**
     * No content response 
     */

    protected function noContentResponse(string $message = 'Resource deleted successfully')
    {
        return response()->json([
            'success' => true , 
            'message' => $message ,
            'timestamp' => now()->toIso8601String(),
        ] , 200);
    }
}


