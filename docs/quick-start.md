## Quick Start

### 1. Define File Types
Create an enumeration FileType to represent different types of files.

```php
<?php
namespace App\Enums;

use Imahmood\FileStorage\Contracts\MediaTypeInterface;

enum FileType: int implements MediaTypeInterface
{
    case USER_AVATAR = 1;
    
    public function identifier(): int
    {
        return $this->value;
    }
}
```

### 2. Preparing your model
Ensure your  model  correctly implements the `MediaAwareInterface` and define a relation to retrieve the uploaded file.

```php
<?php
namespace App\Models;

use App\Enums\FileType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Imahmood\FileStorage\Contracts\MediaAwareInterface;
use Imahmood\FileStorage\MediaAwareTrait;
use Imahmood\FileStorage\Models\Media;

class User extends Model implements MediaAwareInterface
{
    use MediaAwareTrait;
    
    public function avatar(): MorphOne
    {
        return $this
            ->morphOne(Media::class, 'model')
            ->where(['type' => FileType::USER_AVATAR]);
    }
}
```

### 3. Uploading files

```php
<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Imahmood\FileStorage\FileStorage;

class UsersController extends Controller
{
    public function __construct(
        private readonly FileStorage $fileStorage,
    ) {
    }
    
    public final store(Request $request, int $userId)
    {
        $user = User::query()->findOrFail($userId);
        
        $media = $this->fileStorage->create(
            type: FileType::USER_AVATAR,
            relatedTo: $user,
            media: $user->avatar,
            uploadedFile: $request->file('avatar'),
        );
        
        // ...
    }
}
```
