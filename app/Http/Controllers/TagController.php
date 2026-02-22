<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Resources\TagResource;
use App\Services\TagService;

class TagController extends Controller
{
    public function __construct(protected TagService $tagService)
    {}

    public function index()
    {
        return $this->successResponse(
            TagResource::collection($this->tagService->getAllTags()),
            'Tags retrieved successfully'
        );
    }
    public function store(StoreTagRequest $request)
    {
        $data = $request->validated();

        $tag = $this->tagService->createTag($data);

        return $this->createResponse(
            new TagResource($tag),
            'Tag created successfully'
        );
    }
}
