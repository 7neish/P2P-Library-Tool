<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/Category.php';

class CategoryController {

public function findAll() {
    $db = DBController::getInstance();
    $conn = $db->getConnection();
    $res = mysqli_query($conn, "SELECT * FROM categories ORDER BY level, name");
    $categories = [];
    
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $cat = new Category();
            $cat->category_id = $row['category_id']; 
            $cat->name        = $row['name'];
            $cat->level       = $row['level'];
            $cat->parent_id   = $row['parent_id'] ?? null;
            $categories[] = $cat;
        }
    }
    return $categories;
}

    public function findById($id) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$id;
        $res = mysqli_query($conn, "SELECT * FROM categories WHERE category_id=$id");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $cat = new Category();
            foreach ($row as $k => $v) {
                if (property_exists($cat, $k)) $cat->$k = $v;
            }
            return $cat;
        }
        return null;
    }

    public function tree() {
        $all = $this->findAll();
        $byParent = [];
        foreach ($all as $c) {
            $byParent[$c->parent_id ?? 0][] = $c;
        }
        return $byParent;
    }
}