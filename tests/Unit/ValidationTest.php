<?php

namespace Tests\Unit;

use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\CreateFollowRequest;
use App\Http\Requests\CreateLikeRequest;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdateCommentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_post_request_validates_caption_is_required(): void
    {
        $request = new CreatePostRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('caption'));
    }

    public function test_create_post_request_validates_caption_max_length(): void
    {
        $request = new CreatePostRequest();
        $validator = Validator::make([
            'caption' => str_repeat('a', 101),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('caption'));
    }

    public function test_create_post_request_validates_image_is_file(): void
    {
        $request = new CreatePostRequest();
        $validator = Validator::make([
            'caption' => 'Test',
            'image' => 'not-a-file',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('image'));
    }

    public function test_create_post_request_accepts_valid_data(): void
    {
        $request = new CreatePostRequest();
        $validator = Validator::make([
            'caption' => 'Valid caption',
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_create_comment_request_validates_post_id_is_required(): void
    {
        $request = new CreateCommentRequest();
        $validator = Validator::make([
            'content' => 'Test comment',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('post_id'));
    }

    public function test_create_comment_request_validates_post_id_is_uuid(): void
    {
        $request = new CreateCommentRequest();
        $validator = Validator::make([
            'post_id' => 'not-a-uuid',
            'content' => 'Test comment',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('post_id'));
    }

    public function test_create_comment_request_validates_content_is_required(): void
    {
        $request = new CreateCommentRequest();
        $validator = Validator::make([
            'post_id' => '9fc3580f-51f3-44e9-b67d-1159a859c97a',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('content'));
    }

    public function test_create_comment_request_validates_content_max_length(): void
    {
        $request = new CreateCommentRequest();
        $validator = Validator::make([
            'post_id' => '9fc3580f-51f3-44e9-b67d-1159a859c97a',
            'content' => str_repeat('a', 101),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('content'));
    }

    public function test_create_comment_request_accepts_valid_data(): void
    {
        $request = new CreateCommentRequest();
        $validator = Validator::make([
            'post_id' => '9fc3580f-51f3-44e9-b67d-1159a859c97a',
            'content' => 'Valid comment',
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_update_comment_request_validates_content_is_required(): void
    {
        $request = new UpdateCommentRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('content'));
    }

    public function test_update_comment_request_validates_content_max_length(): void
    {
        $request = new UpdateCommentRequest();
        $validator = Validator::make([
            'content' => str_repeat('a', 101),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('content'));
    }

    public function test_update_comment_request_accepts_valid_data(): void
    {
        $request = new UpdateCommentRequest();
        $validator = Validator::make([
            'content' => 'Valid updated comment',
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_create_like_request_validates_post_id_is_required(): void
    {
        $request = new CreateLikeRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('post_id'));
    }

    public function test_create_like_request_validates_post_id_is_uuid(): void
    {
        $request = new CreateLikeRequest();
        $validator = Validator::make([
            'post_id' => 'not-a-uuid',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('post_id'));
    }

    public function test_create_like_request_accepts_valid_data(): void
    {
        $request = new CreateLikeRequest();
        $validator = Validator::make([
            'post_id' => '9fc3580f-51f3-44e9-b67d-1159a859c97a',
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_create_follow_request_validates_to_id_is_required(): void
    {
        $request = new CreateFollowRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('to_id'));
    }

    public function test_create_follow_request_validates_to_id_is_uuid(): void
    {
        $request = new CreateFollowRequest();
        $validator = Validator::make([
            'to_id' => 'not-a-uuid',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('to_id'));
    }

    public function test_create_follow_request_accepts_valid_data(): void
    {
        $request = new CreateFollowRequest();
        $validator = Validator::make([
            'to_id' => '9fc3580f-51f3-44e9-b67d-1159a859c97a',
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }
}
