# LostMate AI — Defense Demo Script (about 12 minutes)

## Before the defense (the day before)

- [ ] Heroku **Resources**: `web` and `worker` are ON. Open the site once.
- [ ] Admin dashboard → **System health** says "All good".
- [ ] Prepare 3 accounts, each verified and logged in on a separate
      browser or private window:
  - **Owner** (student who lost something)
  - **Finder** (student who found it)
  - **Admin** (your account)
- [ ] Have one photo of a "lost" object ready on the laptop or phone.
- [ ] Phone on the same internet, with the site open (for the QR and
      install demo).

> Tip: AI matching takes a few seconds to a minute. Do steps 2 and 3
> early, then present the slides while it runs.

## 1. Landing page and registration (1 min)

1. Open the site logged out: the landing page shows only totals, never
   item details.
2. Show **Register**: mention school-email verification. Open the
   verification email on the phone.
3. Point out the **Privacy** link (Data Privacy Act of 2012).

## 2. Finder reports a found item (2 min) — *Finder window*

1. **Report → I found something**: "Black leather wallet", category
   Wallets, color black, location "Library entrance", today's date.
2. **Hidden details**: "Contains a school ID and ₱500". Explain: only the
   finder and admins ever see this; it's how ownership is proven.
3. Add the photo: point out the preview and "photos were resized,
   location data removed".

## 3. Owner reports the lost item (1 min) — *Owner window*

1. **Report → I lost something**: "Black wallet with a red logo",
   Wallets, black, "Library 2nd floor".
2. Point out **"AI is checking for matches…"** on the report.

## 4. AI match (2 min)

1. Refresh: the match appears with a score and a short reason.
2. Explain the pipeline (see SYSTEM_OVERVIEW §3): MySQL pre-filter →
   only item details sent → JSON validated → color rule enforced in code.
3. Stress: **suggestions only** — people confirm ownership.

## 5. Message and claim (2 min)

1. Owner clicks the finder's name → **public profile** (no email/phone).
2. Owner sends a message: "I think this is my wallet".
3. Owner clicks **Claim this item**, describes: "My school ID and ₱500
   inside".
4. *Finder window*: **View Claims** → compare with hidden details →
   **Approve**.

## 6. Safe handover with pickup code (1 min)

1. *Owner window*: **My Claims** shows the 6-digit pickup code.
2. *Finder window*: enter a wrong code → rejected. Enter the right code
   → item **returned**. Both sides get notified.

## 7. Admin side (2 min) — *Admin window*

1. **Dashboard**: stats, System health, reports + recovery-rate chart.
2. **Office & Unclaimed**: mark an item received at the office →
   **Print claim tag** → scan the QR with the phone.
3. **Reports & Export**: download the CSV (no private fields).
4. **Admin Logs**: every action above is recorded.

## 8. Extras if time allows (1 min)

- Toggle **dark mode**.
- On the phone: **Install app** to the home screen.
- Profile → **Delete my account** (explain the safety checks; don't
  actually click it).

## Likely panel questions

| Question | Short answer |
|---|---|
| What if the AI is wrong? | It only suggests. Claims need hidden-detail proof and a pickup code. |
| What if the AI service is down? | Reports still save; jobs retry 3 times; admins can retry from System health. |
| How do you protect privacy? | Email/phone never public, hidden details never shown or sent to AI, photo GPS removed, privacy notice, account deletion. |
| How do you stop false claims? | Hidden details + finder review + one-time pickup code at handover. |
| Why a queue? | AI and email are slow/unreliable; the user never waits for them. |
| How is it tested? | 168 automated tests; external services are faked. |
