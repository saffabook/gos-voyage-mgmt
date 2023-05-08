<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_post_successful()
    {
        $post = [
            'title' => 'Testing Title',
            'author' => 'Testing Author'
        ];

        $response = $this->postJson('/api/posts/add', $post);

        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', $post);

        $lastPost = Post::latest()->first();
        $this->assertEquals($post['title'], $lastPost->title);
        $this->assertEquals($post['author'], $lastPost->author);
    }

    public function test_api_post_store_successful()
    {
        $post = [
            'title' => 'Test Title',
            'author' => 'Test Author'
        ];

        $response = $this->postJson('/api/posts/add', $post);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => $post
        ]);
    }

    public function test_api_post_invalid_store_returns_error()
    {
        $post = [
            'title' => '',
            'author' => 'Test Author'
        ];

        $response = $this->postJson('/api/posts/add', $post);

        $response->assertStatus(422);
    }

    public function test_api_returns_posts_list()
    {
        $post = Post::factory()->create();

        $response = $this->getJson('/api/posts/');

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [$post->toArray()]
        ]);
    }

    public function test_post_update_validation_error()
    {
        $post = Post::factory()->create();

        $response = $this->post('/api/posts/update/' . $post->id, [
            'title' => '',
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure(['error' => ['title']]);
    }

    public function test_post_delete_successful()
    {
        $post = Post::factory()->create();

        $response = $this->post('api/posts/delete/' . $post->id);

        $response->assertStatus(200)->assertJsonStructure(['data']);

        $this->assertDatabaseMissing('posts', $post->toArray());
        $this->assertDatabaseCount('posts', 0);
    }
}
