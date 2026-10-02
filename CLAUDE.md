# LostMate AI — Project Context

## What this is
LostMate AI is a web-based lost and found management system for a school.
Students and staff report lost or found items, search reports, receive
AI-suggested possible matches, message each other, and submit claims.
Administrators manage users, reports, claims, categories, and flagged content.

This is a school capstone project. Code must be clean, well-commented, and
easy for the student developers to explain during their defense.

## Tech stack (do not change without asking)
- Backend: PHP, Laravel (latest stable), MVC, Eloquent ORM
- Frontend: Blade templates, Bootstrap 5, vanilla JavaScript (no React/Vue)
- Database: MySQL, using migrations and seeders only (no raw SQL dumps)
- AI: OpenAI-compatible chat completions API, called only from the backend.
  Talks to real OpenAI by default; can point at a local Ollama server
  instead (free, no API costs) via OPENAI_BASE_URL - see "AI matching
  rules" below.
- Queue: Laravel database queue driver (QUEUE_CONNECTION=database)
- Notifications: Laravel's built-in database notifications
- Local dev: localhost (XAMPP or Laragon), Git/GitHub for version control.
  Run `composer dev` - it starts the web server, queue worker (required
  for AI matching), scheduler, and Vite together.
- Tests never touch the network: tests/TestCase.php calls
  Http::preventStrayRequests(), and phpunit.xml pins AI settings.

## Full database schema
See `docs/DATABASE_SCHEMA.md`. Follow it exactly for table names, columns,
types, and relationships. Ask before adding or renaming columns.

## User roles
- student_staff: report lost/found items, edit/delete own reports, search,
  view matches, message other users, submit and respond to claims,
  flag inappropriate content
- admin: everything above, plus manage users, reports, claims, categories,
  flagged content, and view admin logs

## Item statuses and allowed transitions
Statuses: open, matched, claimed, returned, closed

- open → matched: owner confirms an AI match or a claim is submitted
- matched → open: match dismissed or claim rejected
- matched → claimed: finder approves a claim
- claimed → returned: finder confirms the item was handed over
- returned → closed: owner confirms receipt, or auto-close after 7 days
- open → closed: reporter withdraws the report, or admin closes it
Any other transition must be rejected. Put this logic in one place
(e.g., an ItemStatusService or model methods), not scattered in controllers.

When a found item becomes returned, the linked lost item (if any) also
becomes returned.

## AI matching rules
- AI results are SUGGESTIONS ONLY. Never auto-confirm ownership.
- Matching runs in a queued job after a lost or found report is created
  or its key details are edited.
- Pre-filter candidates in MySQL BEFORE calling the AI:
  opposite report type, status open or matched, same category (or
  "Others"), and date within 30 days. Send at most 15 candidates.
- Send only text details: item name, category, color, brand, location,
  date, description. NEVER send hidden_details, user names, emails,
  or phone numbers to the AI.
- Exception (approved, opt-in, off by default): AI_MATCHING_PHOTOS=true
  also sends the FIRST photo of each report at "low" detail. The system
  prompt tells the AI never to read out personal info visible in photos.
  Needs a vision model (gpt-4o-mini; not llama3.2:3b).
- The AI must return strict JSON only, in this shape:
  {"matches": [{"candidate_id": 12, "score": 85, "reason": "short text"}]}
- Validate the JSON. Discard candidate ids not in the candidate list.
  Save only matches with score >= 50 to ai_matches.
- Hard facts are enforced in code, not trusted to the AI: a match is
  rejected if both reports list a color and the colors share no word.
- When matching re-runs, "suggested" matches the new run no longer
  supports are removed. Dismissed/confirmed matches are never touched.
- If the AI call fails or returns invalid JSON, the report still saves
  successfully. Log the error and allow a retry.
- Config in .env only: OPENAI_API_KEY, OPENAI_MODEL, OPENAI_BASE_URL. Never
  hardcode the key or expose it to the frontend. Read it through
  config/services.php.
- To run matching against a free local model instead of paid OpenAI
  credits: install Ollama (ollama.com), `ollama pull llama3.2:3b`, then
  set AI_MATCHING_FAKE=false, OPENAI_API_KEY to any placeholder value
  (Ollama ignores it), OPENAI_MODEL=llama3.2:3b, and
  OPENAI_BASE_URL=http://localhost:11434/v1. The Ollama service must be
  running (it starts automatically on Windows after install/login, or run
  `ollama serve`).
- Put all AI code in app/Services/MatchingService.php.

## Security and privacy rules
- Use Laravel validation (Form Request classes), CSRF protection, and
  Policies for authorization.
- Users can only edit or delete their own reports, messages, and claims.
- found_items.hidden_details is NEVER shown publicly, never shown to
  claimants, and never sent to the AI. Only the finder and admins see it.
  Claimants must describe identifying details in their claim so the finder
  can compare.
- profiles.contact_number and users.email are never shown publicly.
  Users communicate through in-app messaging.
- Image uploads: jpg, jpeg, png, webp only; max 5 MB each; max 3 per item.
  Store with Laravel's public disk and generated filenames.
- Deactivated users (is_active = false) cannot log in.
- Admin routes are protected by an admin middleware.
- Log every admin action to admin_logs.

## Coding conventions
- Controllers stay thin. Business logic goes in app/Services.
- Use Form Requests for validation, Policies for authorization,
  Enums (PHP 8.1+) for statuses and roles.
- Use named routes and route model binding.
- Use Blade components/partials for repeated UI (item cards, status badges,
  forms, flash messages).
- Paginate all lists (12 items per page for browsing, 20 for admin tables).
- Mobile-responsive layout using Bootstrap grid.
- Comment non-obvious logic in plain English.

## How to work with me
- Build one feature at a time. Do not start the next phase unless I ask.
- Before writing code for a phase, briefly list the files you will create
  or change.
- After each feature, tell me exactly how to test it in the browser and
  which commands to run (migrations, seeders, queue worker).
- Explain what you changed and why, in simple terms.
- Ask before adding new packages or changing the stack.
- Remind me to commit to Git after each working feature.
