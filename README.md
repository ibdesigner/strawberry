strawberry
==========

A helper Class for Wordpress


## TO DO
* enable/disable cache based on WP_CACHE

## HOW TO USE

put the file in your theme directory
include it in functions.php file and add this:

```php
$posts = Strawberry::cache(300)->posts(array('category_name'=> 'Articles', 'posts_per_page' => 3));
```

in your theme files add

```php
foreach($posts as $post){
    echo $post["title"]; // post title
    echo $post["excerpt"]; // wordpress generated excerpt
    echo $post["content_excerpt"]; // content striped excerpt
    echo $post['meta']['some_meta_name']; // some post meta value
    echo $post['thumb']['some_thumb_size_name']['src']; // feature image src

    // list all images with certain size
    foreach($post['images'] as $image){
        echo $image['some_thumb_size_name']['src'];
    }
}
```
