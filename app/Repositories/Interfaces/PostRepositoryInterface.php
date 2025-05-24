<?php

namespace App\Repositories\Interfaces;

interface PostRepositoryInterface
{
    public function all(array $filters = []);
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function getScheduledPosts();
    public function getPostsByUser(int $userId, array $filters = []);
    public function attachPlatforms(int $postId, array $platformIds);
    public function detachPlatforms(int $postId, array $platformIds);
}
