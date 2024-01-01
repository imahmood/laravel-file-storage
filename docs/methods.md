## Methods

The `\Imahmood\FileStorage\FileStorage` class offers several methods:

```php
// Specify the disk where the file will be stored.

$this->fileStorage->onDisk($disk);

$this->fileStorage
    ->onDisk($disk)
    ->create($type, $relatedTo, $uploadedFile);
```

```php
// Create and associate a new file.

$this->fileStorage->create($type, $relatedTo, $uploadedFile);
```

```php
// Update the existing file.

$this->fileStorage->update($type, $relatedTo, $media, $uploadedFile);
```

```php
// Create a new file or update an existing one.

$this->fileStorage->updateOrCreate($type, $relatedTo, $media, $uploadedFile);
```

```php
// Remove the media instance from both the database and the disk.

$this->fileStorage->delete($media);
```
