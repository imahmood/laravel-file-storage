## Getting a file URL

```php
// Retrieve the media instance from the specified relation in the model.

$media = $user->avatar;
```

```php
// Gets the URL to the original file.
echo $media->original_url;

// Gets the URL to the preview version of the file.
echo $media->preview_url;

// Gets the relative path to the original file.
echo $media->original_relative_path;

// Gets the absolute path to the original file.
echo $media->original_absolute_path;

// Gets the relative path to the preview version of the file.
echo $media->preview_relative_path;

// Gets the absolute path to the preview version of the file.
echo $media->preview_absolute_path;
```
