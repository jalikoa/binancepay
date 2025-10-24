<?php
namespace App\Abstracts;

abstract class AbstractRepository {
    protected $model;
    public function __construct($model) {
        $this->model = $model;
    }
}