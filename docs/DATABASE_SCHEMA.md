# LostMate AI — Database Schema

All tables use Laravel conventions: `id` is an auto-increment BIGINT primary
key, and `timestamps` means `created_at` and `updated_at`. FK = foreign key.

Tables: users, profiles, categories, lost_items, found_items, item_images,
ai_matches, conversations, messages, claims, notifications, reports,
admin_logs (plus Laravel's default password_reset_tokens, sessions, jobs,
failed_jobs, cache tables).

---

## 1. users
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar(255) | |
| email | varchar(255) | unique, never shown publicly |
| student_id | varchar(50) | nullable, unique (school ID number) |
| password | varchar(255) | hashed |
| role | enum('student_staff','admin') | default 'student_staff' |
| is_active | boolean | default true; false = cannot log in |
| email_verified_at | timestamp | nullable |
| remember_token | varchar(100) | nullable |
| timestamps | | |

## 2. profiles
One-to-one with users.
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users.id | unique, cascade on delete |
| department | varchar(150) | nullable |
| course_or_position | varchar(150) | nullable (e.g., BSIT, Faculty) |
| year_level | varchar(20) | nullable |
| contact_number | varchar(20) | nullable, private |
| avatar_path | varchar(255) | nullable |
| bio | text | nullable |
| timestamps | | |

## 3. categories
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar(100) | unique |
| slug | varchar(120) | unique |
| description | varchar(255) | nullable |
| is_active | boolean | default true |
| timestamps | | |

Seed: Wallets, IDs, Phones, Gadgets, Books & Notebooks, Bags, Keys,
Accessories, Clothing, Others.

## 4. lost_items
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users.id | owner/reporter, cascade on delete |
| category_id | FK → categories.id | restrict on delete |
| item_name | varchar(150) | |
| color | varchar(50) | nullable |
| brand | varchar(100) | nullable |
| description | text | |
| location_lost | varchar(255) | e.g., "Library 2nd floor" |
| date_lost | date | |
| time_lost | time | nullable |
| status | enum('open','matched','claimed','returned','closed') | default 'open' |
| closed_at | timestamp | nullable |
| timestamps | | |
| deleted_at | timestamp | soft deletes |

Indexes: (category_id, status), date_lost.

## 5. found_items
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users.id | finder/reporter, cascade on delete |
| category_id | FK → categories.id | restrict on delete |
| item_name | varchar(150) | |
| color | varchar(50) | nullable |
| brand | varchar(100) | nullable |
| description | text | public description |
| hidden_details | text | nullable, PRIVATE (finder + admin only; never sent to AI) |
| location_found | varchar(255) | |
| date_found | date | |
| time_found | time | nullable |
| current_location | varchar(255) | nullable (e.g., "With finder", "Guidance Office") |
| status | enum('open','matched','claimed','returned','closed') | default 'open' |
| closed_at | timestamp | nullable |
| timestamps | | |
| deleted_at | timestamp | soft deletes |

Indexes: (category_id, status), date_found.

## 6. item_images
Polymorphic: belongs to either a lost_item or a found_item.
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| imageable_type | varchar(255) | App\Models\LostItem or App\Models\FoundItem |
| imageable_id | bigint | |
| path | varchar(255) | storage path |
| original_name | varchar(255) | nullable |
| timestamps | | |

Max 3 images per item. Index: (imageable_type, imageable_id).

## 7. ai_matches
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| lost_item_id | FK → lost_items.id | cascade on delete |
| found_item_id | FK → found_items.id | cascade on delete |
| score | tinyint unsigned | 0–100 |
| reason | text | AI's short explanation |
| status | enum('suggested','confirmed','dismissed') | default 'suggested' |
| actioned_by | FK → users.id | nullable, who confirmed/dismissed |
| model_used | varchar(100) | nullable |
| timestamps | | |

Unique: (lost_item_id, found_item_id). Re-running matching updates the
existing row instead of duplicating it (unless status is 'dismissed').

## 8. conversations
A private two-person thread, usually about a specific item or match.
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_one_id | FK → users.id | starter |
| user_two_id | FK → users.id | recipient |
| lost_item_id | FK → lost_items.id | nullable, set null on delete |
| found_item_id | FK → found_items.id | nullable, set null on delete |
| ai_match_id | FK → ai_matches.id | nullable, set null on delete |
| last_message_at | timestamp | nullable |
| timestamps | | |

Only the two participants (and admins reviewing a flag) can view it.

## 9. messages
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| conversation_id | FK → conversations.id | cascade on delete |
| sender_id | FK → users.id | |
| body | text | max 2000 chars |
| read_at | timestamp | nullable |
| timestamps | | |

## 10. claims
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| found_item_id | FK → found_items.id | cascade on delete |
| claimant_id | FK → users.id | |
| lost_item_id | FK → lost_items.id | nullable (claimant's own lost report, if any) |
| ai_match_id | FK → ai_matches.id | nullable |
| identifying_details | text | claimant describes marks, contents, etc. |
| proof_image_path | varchar(255) | nullable (e.g., old photo with item) |
| status | enum('pending','approved','rejected','cancelled','completed') | default 'pending' |
| finder_response | text | nullable, reason for approval/rejection |
| reviewed_at | timestamp | nullable |
| completed_at | timestamp | nullable (item handed over) |
| timestamps | | |

Rules: one pending claim per claimant per found item; a claimant cannot
claim their own found item; only one claim per item can be approved.

## 11. notifications
Use Laravel's built-in table (`php artisan make:notifications-table`).
| Column | Type | Notes |
|---|---|---|
| id | uuid PK | |
| type | varchar(255) | notification class |
| notifiable_type / notifiable_id | morphs | the user |
| data | json/text | message, link, item id |
| read_at | timestamp | nullable |
| timestamps | | |

Notification classes: NewPossibleMatch, NewMessage, ClaimSubmitted,
ClaimApproved, ClaimRejected, ItemReturned, ReportActionTaken.

## 12. reports (flagged content)
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| reporter_id | FK → users.id | |
| reportable_type / reportable_id | morphs | LostItem, FoundItem, Message, or User |
| reason | enum('spam','inappropriate','fraud','false_claim','other') | |
| details | text | nullable |
| status | enum('pending','reviewed','action_taken','dismissed') | default 'pending' |
| reviewed_by | FK → users.id | nullable (admin) |
| reviewed_at | timestamp | nullable |
| admin_notes | text | nullable |
| timestamps | | |

## 13. admin_logs
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| admin_id | FK → users.id | |
| action | varchar(100) | e.g., user.deactivated, claim.overridden |
| target_type | varchar(255) | nullable |
| target_id | bigint | nullable |
| description | text | nullable |
| ip_address | varchar(45) | nullable |
| created_at | timestamp | |

---

## Relationships summary
- User hasOne Profile; hasMany LostItem, FoundItem, Claim (as claimant),
  Message (as sender), Report (as reporter)
- Category hasMany LostItem, FoundItem
- LostItem / FoundItem belongTo User and Category; morphMany ItemImage;
  morphMany Report
- LostItem hasMany AiMatch; FoundItem hasMany AiMatch and Claim
- AiMatch belongsTo LostItem and FoundItem
- Conversation hasMany Message; belongsTo two Users
- Claim belongsTo FoundItem, User (claimant), and optionally LostItem, AiMatch

## Seed data (for testing and demo)
- 1 admin account, 6 student/staff accounts with profiles
- All categories
- ~10 lost items and ~10 found items with realistic school locations
  (Library, Canteen, Gym, Room 204, Parking Lot, etc.), including this
  intended match pair:
  - Lost: "Black leather wallet with a red logo", lost near the library
  - Found: "Black wallet with a red mark", found near the library entrance,
    hidden_details: "Contains a school ID and a jeepney card"
