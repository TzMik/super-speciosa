# Take Home Test

- [Purpose](#purpose)
- [Scenario](#scenario)
- [Part 1 — Debug & Review](#part-1-debug--review)
- [Part 2 — Data Modeling (15 minutes)](#part-2-data-modeling-15-minutes)
- [Part 3 — Feature Implementation](#part-3-feature-implementation)
- [Part 4 — Engineering Thinking](#part-4-engineering-thinking)
- [Submission](#submission)

---

## Purpose
This exercise simulates a real engineering problem at Super Organics.

We are testing:
- Laravel architecture decisions
- Database design thinking
- Debugging ability
- Ability to explain tradeoffs
- Speed of shipping pragmatic solutions

> [!NOTE]
> Using AI tools is allowed, but you must explain your reasoning.

### Deliverables
- A README with answers to the questions.
- Code that we can run.

---

## Scenario
Super Organics is building a B2B lead CRM for wholesale partners.
A basic system was started but it does not scale well and has bugs.

Your task is to:
- Identify problems
- Improve the architecture
- Implement one key feature

---

## Part 1 — Debug & Review
Below is a simplified controller currently used in production.

```php
class LeadController extends Controller
{
   public function index()
   {
       $leads = DB::table('leads')->get();

       foreach ($leads as $lead) {
           $lead->user = DB::table('users')
               ->where('id', $lead->assigned_user_id)
               ->first();
       }

       return response()->json($leads);
   }
}
```

### Questions
Answer the following:

> ANSWER
1. What performance problem exists in this code?
    - There are three main performance issues in this controller:
        - The first problem is that a query is being performed for each lead to get its assigned user, resulting in an N+1 queries problem. If there are 1M leads, 1M additional queries would be performed to get the assigned users, which can result in very slow performance.
        - The second problem is that the `DB::table()` function is being used to perform queries, which can result in slow performance if a large database is used.
        - The third problem is that all leads are being retrieved from the database, which can result in very slow performance if a large database is used.
2. How would you fix it in Laravel?
    - To solve these problems, the following improvements can be made:
        - Instead of performing a query for each lead to get its assigned user, you can use Eloquent's `with()` function to perform a single query that gets all leads and their assigned users. The `with` function makes two queries: one to get all leads and another to get all assigned users of the retrieved leads, using `SELECT * FROM users WHERE id IN (1, 2, 3, ...)` with the IDs of the retrieved `assigned_user_id` column. For `with` to work, it is necessary for the `belongsTo` relationship to exist between `leads` and `users`.
        - Instead of using the `DB::table()` function, the `Lead` model can be used to perform queries, which will result in much better performance.
        - Instead of getting all leads from the database, you can use Eloquent's `paginate()` function to create a pagination of the leads, which will result in much better performance.
3. If the table grows to 1M leads, what database changes would you consider?
    - To optimize the database, the following improvements can be made:
        - The improvements mentioned in the previous question:
            1. Use Eloquent's `with()` function to perform a single query that gets all leads and their assigned users.
            2. Use the `Lead` model to perform queries.
            3. Use Eloquent's `paginate()` function to create a pagination of the leads.
        - Create an index on the `assigned_user_id` column to improve query performance.
        - Create grouped indexes on the columns used to filter and sort leads, such as `assigned_user_id` and `created_at`.

*Write your answer in plain English.*

---

## Part 2 — Data Modeling (15 minutes)
We want to add a feature:
Sales reps should be able to add notes to a lead.

**Example:**
- **Lead:** Green Earth Market
- **Notes:**
    - Called buyer, interested in organic line
    - Wants wholesale pricing sheet

### Task
Design the database schema for this feature.

**Provide:**
- Table structure
- Indexes
- Laravel migration

**Example format:**
`lead_notes`
- `id`
- `lead_id`
- `user_id`
- `note`
- `created_at`

> ANSWER 

```sql
/*
    This table will have an auto-incrementing id, a lead_id which is the foreign key to the leads table, a user_id which is the foreign key to the users table, a note which is the text of the note, a created_at which is the creation date of the note, and an updated_at which is the update date of the note.

    The lead_id foreign key points to the leads table and has an ON DELETE CASCADE, which means that if a lead is deleted, all notes associated with that lead are also deleted.

    The user_id foreign key points to the users table and has an ON DELETE RESTRICT, which means it will prevent deleting a user if they have associated notes.

    The idx_lead_notes_created_at index is a composite index created on the lead_id and created_at columns, allowing for fast and efficient searches on the lead_notes table based on the lead_id and created_at.
*/
CREATE TABLE lead_notes (
    id INT PRIMARY KEY,
    lead_id INT,
    user_id INT,
    note TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_lead_notes_created_at (lead_id, created_at)
);

/*
  To conclude, I would like to mention that 
  3 indexes are being created in total: the ones 
  for the foreign keys and the composite index 
  on the lead_id and created_at columns.
*/
```

**Also answer:**
- How would you query the latest note for each lead efficiently?

> ANSWER

If we need to obtain the latest lead_note object
itself, we can create a function in Lead model
with the following code:
```php
public function latestNote()
{
    return $this->hasOne(LeadNote::class)->latestOfMany();
}
```

And then retrive the latest note like this in the controller:
```php
$leads = Lead::with(['user', 'latestNote'])->get();

return response()->json($leads);
```
But if we only need the latest note text, we can use the following code directly in the controller:
```php
$leads = Lead::with('user')
    ->withAggregate('latestNote as last_note_content', 'note')
    ->get();

return response()->json($leads);
```
---

## Part 3 — Feature Implementation
Spin up a Laravel application for this and include it in your submission.

**Implement an API endpoint:**
`POST /api/leads/{lead}/notes`

**Example request:**
```json
{
 "note": "Buyer wants to review wholesale pricing."
}
```

### Requirements:
- Save the note
- Associate with logged-in user
- Return the created note

### Expected pieces:
- Model
- Migration
- Controller method
- Validation

> [!TIP]
> You do not need to build authentication. Assume `Auth::user()` exists.

> ANSWER in [part-3](./part-3/README.md) subdirectory (laravel project)
---

## Part 4 — Engineering Thinking
Answer briefly:
1. If this CRM grows to 50 sales reps and 500k leads, what would you improve next?

> ANSWER

I managed a database with over 20 million records, and from experience, I know that relational databases perform very effectively using indexes for up to about a million records per table.

However, if the server load becomes too high, we should consider implementing filters to restrict users so they can only view their own leads and notes. This significantly reduces the overhead required to fetch data, while reserving global data access for administrators only.

If this limit isn't enough, we could consider vertical scaling by upgrading the hardware (budget permitting and assuming it’s a viable long-term strategy for future growth). Alternatively, we could begin decoupling modules into microservices to offload the primary server.

If the database itself is the bottleneck, which is usually the case in these scenarios, we could implement a Master-Slave (Primary-Replica) architecture. This allows users to read information from database replicas while restricting write operations to the central master server.

2. Where could AI automation help this CRM?

> ANSWER


