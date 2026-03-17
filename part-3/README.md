# Part 3 - Answer

## Installation
- Install dependencies: `composer install`.
- Install migrations and seed the database: `php artisan migrate --seed` (or `php artisan migrate:fresh --seed` to reset the database).
- Run the server locally: `php artisan serve`

## How to test
Use `Postman` or `Bruno` to make a call to `POST http://localhost:8000/api/leads/:leadId/notes`.

Change the `:leadId` URI param with the id of the Lead where you want to insert the note.

Send a valid JSON in the request body. This is an example of a valid body:

```json
{
  "note": "Example 2"
}
```

## Walkthrough
### 1. Migrations:
- [`create_leads_table.php`](./database/migrations/2026_03_16_190000_create_leads_table.php): Creates the `leads` table with `title`, `assigned_user_id`, and `client_id` columns.
- [`create_lead_notes_table.php`](./database/migrations/2026_03_16_190001_create_lead_notes_table.php): Creates the `lead_notes` table with `lead_id`, `user_id`, and `note` columns.
- The result of `php artisan migrate --pretend` is (to view the real queries):

```
2026_03_16_190000_create_leads_table   
⇂ create table "leads" ("id" integer primary key autoincrement not null, "title" varchar not null, "assigned_user_id" integer not null, "client_id" integer, "created_at" datetime, "updated_at" datetime, foreign key("assigned_user_id") references "users"("id"))  
⇂ create index "leads_client_id_index" on "leads" ("client_id")  

2026_03_16_190001_create_lead_notes_table   
⇂ create table "lead_notes" ("id" integer primary key autoincrement not null, "lead_id" integer not null, "user_id" integer not null, "note" text not null, "created_at" datetime, "updated_at" datetime, foreign key("lead_id") references "leads"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete restrict)  
⇂ create index "idx_lead_notes_created_at" on "lead_notes" ("lead_id", "created_at")
```

### 2. Models:
- Change [`User`](./app/Models/User.php) model inserting the next code inside the `User` class:
```php
/**
 * Get the leads assigned to the user.
 */
public function leads(): HasMany
{
    // We specify 'assigned_user_id' because it deviates from the default 'user_id'
    return $this->hasMany(Lead::class, 'assigned_user_id');
}

/**
 * Get the notes created by the user.
 */
public function notes(): HasMany
{
    return $this->hasMany(LeadNote::class);
}
```

- Create a new model for [`Lead`](./app/Models/Lead.php).
- Create a new model for [`LeadNote`](./app/Models/LeadNote.php).

### 3. Seeder

- For testing I create a `seeder` for create 10 random `Leads`: [`LeadSeeder`](./database/seeders/LeadSeeder.php).

### 4. Router
- I installed the [`api.php`](./routes/api.php) router using: `php artisan install:api`.
- Then I create an endpoint `/leads/{lead}/notes` pointing to `LeadNoteController`.

### 5. Controller
- I create the [`LeadNoteController`](./app/Http/Controllers/LeadNoteController.php) controller for execute a `POST` request. This request gets the request object itself and the Lead where we have to insert the note.

### 6. Validations
- I validate that verifies that the note field exists inside the JSON body, and we check that this values is not empty and doesn't exceed the maximun character limit.

- I use the `Lead` model as the second parameter for the function. With this, Laravel checks automatically that the given `lead_id` exists in the database.

## Tests made
- Make a call with an invalid JSON body: `422 Unprocessable Entity`.
- Make a call with an empty note: `422 Unprocessable Entity`.
- Make a call to insert a note in a non existing Lead: `404 Not Found`.
- Successful request: `201 Created`. Response:

```
{
  "lead_id": 1,
  "user_id": 1,
  "note": "Example 2",
  "updated_at": "2026-03-17T02:26:13.000000Z",
  "created_at": "2026-03-17T02:26:13.000000Z",
  "id": 2
}
```
