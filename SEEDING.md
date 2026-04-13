# MatchGo — Database Seeding Guide

## Quick Start

```bash
php artisan migrate:fresh --seed
```

This drops all tables, re-runs all migrations, and seeds the full demo dataset.

---

## Test Accounts

All accounts use the password: **`password`**

| Role        | Name              | Email                  | Access               |
|-------------|-------------------|------------------------|----------------------|
| super_admin | Super Admin       | super@matchgo.id       | Full panel access    |
| admin       | Admin MatchGo     | admin@matchgo.id       | Full panel access    |
| auditor     | Doni Auditor      | auditor@matchgo.id     | Audit & Verifikasi   |
| player      | Budi Santoso      | budi@matchgo.id        | Player dashboard     |
| player      | Rizky Pratama     | rizky@matchgo.id       | Player dashboard     |
| player      | Andi Kurniawan    | andi@matchgo.id        | Player dashboard     |
| player      | Dimas Prasetyo    | dimas@matchgo.id       | Player dashboard     |
| player      | Fajar Nugroho     | fajar@matchgo.id       | Player dashboard     |
| player      | Galih Wicaksono   | galih@matchgo.id       | Player dashboard     |
| player      | Hendra Gunawan    | hendra@matchgo.id      | Player dashboard     |
| player      | Irfan Hakim       | irfan@matchgo.id       | Player dashboard     |
| player      | Joko Widodo       | joko@matchgo.id        | Player dashboard     |
| player      | Kevin Sanjaya     | kevin@matchgo.id       | Player dashboard     |
| player      | Luthfi Rahman     | luthfi@matchgo.id      | Player dashboard     |
| player      | Muhammad Fauzi    | fauzi@matchgo.id       | Player dashboard     |
| player      | Nanda Putra       | nanda@matchgo.id       | Player dashboard     |
| player      | Oscar Firmansyah  | oscar@matchgo.id       | Player dashboard     |
| player      | Pandu Wijaya      | pandu@matchgo.id       | Player dashboard     |

---

## Role Descriptions

### `super_admin`
The highest privilege level. Intended for the system owner or lead developer.
- Access to all Filament resources
- Can manage user accounts and assign roles (when UserResource is built)
- Can perform bulk deletions and destructive operations
- Login URL: `/admin`

### `admin`
Day-to-day operational staff managing the platform content.
- Can manage venues, venue schedules, teams, matches, and match requests
- Cannot manage user accounts or change roles
- Login URL: `/admin`

### `auditor`
Independent reviewer responsible for verification and score integrity.
- Can **only** access: Team Verification and Match Score Audit menus
- Cannot create, edit, or delete venues / matches / teams
- Verifies new teams before they can use matchmaking
- Approves or disputes match scores before team stats are updated
- Login URL: `/admin`

### `player`
A futsal team captain representing their team on the platform.
- **Cannot** access `/admin` — has a separate player dashboard (not yet built)
- Can use Automatch, Create Match, and Find Match features
- Login URL: `/login` → redirects to `/dashboard` (coming soon)

> **Current state:** player login is not yet available. The player dashboard is planned as the next development phase.

---

## Venues (8 total)

| # | Name                            | City               | Price/hr    | Active |
|---|---------------------------------|--------------------|-------------|--------|
| 1 | Lapangan Futsal Senayan Sport   | Jakarta            | Rp 200.000  | ✅     |
| 2 | Arena Futsal Kemayoran          | Jakarta            | Rp 175.000  | ✅     |
| 3 | Futsal Planet Bekasi            | Bekasi             | Rp 150.000  | ✅     |
| 4 | GOR Futsal Depok Jaya           | Depok              | Rp 130.000  | ✅     |
| 5 | Futsal Kingdom Tangerang        | Tangerang          | Rp 160.000  | ✅     |
| 6 | Lapangan Futsal BSD             | Tangerang Selatan  | Rp 180.000  | ✅     |
| 7 | Futsal Center Bogor             | Bogor              | Rp 120.000  | ✅     |
| 8 | Arena Futsal Grogol (Nonaktif)  | Jakarta            | Rp 140.000  | ❌     |

---

## Teams (6 total)

| Team               | Owner          | City               | Skill Level | Verification |
|--------------------|----------------|--------------------|-------------|--------------|
| Garuda FC          | Budi Santoso   | Jakarta            | competitive | ✅ Verified  |
| Rajawali United    | Galih Wicaksono| Jakarta            | competitive | ✅ Verified  |
| Elang Muda         | Luthfi Rahman  | Bekasi             | semi_pro    | ✅ Verified  |
| Meteor Depok       | Rizky Pratama  | Depok              | semi_pro    | ✅ Verified  |
| Tangerang Warriors | Hendra Gunawan | Tangerang          | casual      | ⏳ Pending   |
| BSD Stars          | Oscar Firmansyah| Tangerang Selatan | casual      | ❌ Rejected  |

---

## Matches (6 total)

| # | Teams                              | Status      | Score | Audit Status |
|---|------------------------------------|-------------|-------|--------------|
| 1 | Garuda FC vs Rajawali United       | completed   | 5–3   | ⏳ Pending   |
| 2 | Elang Muda vs Meteor Depok         | completed   | 2–2   | ✅ Approved  |
| 3 | Tangerang Warriors vs BSD Stars    | ongoing     | —     | —            |
| 4 | Garuda FC vs Elang Muda            | scheduled   | —     | —            |
| 5 | Rajawali United vs Meteor Depok    | scheduled   | —     | —            |
| 6 | Elang Muda vs BSD Stars            | cancelled   | —     | —            |

---

## Audit Data Overview

### Team Verifications

| Team               | Status    | Notes                                                          |
|--------------------|-----------|----------------------------------------------------------------|
| Garuda FC          | verified  | Approved by Doni Auditor                                       |
| Rajawali United    | verified  | Approved by Doni Auditor                                       |
| Elang Muda         | verified  | Approved by Doni Auditor                                       |
| Meteor Depok       | verified  | Approved by Doni Auditor                                       |
| Tangerang Warriors | pending   | Awaiting review                                                |
| BSD Stars          | rejected  | Dokumen pendaftaran tidak lengkap dan data anggota belum memenuhi syarat. |

### Match Score Audits

| Match                        | Score | Audit Status | Auditor      |
|------------------------------|-------|--------------|--------------|
| Garuda FC vs Rajawali United | 5–3   | pending      | —            |
| Elang Muda vs Meteor Depok   | 2–2   | approved     | Doni Auditor |

---

## Team Statistics (post-seed state)

> Stats are only applied after a match score audit is **approved**.
> Match 1 audit is still **pending** — Garuda FC and Rajawali United stats remain at 0.

| Team            | Matches | W | L | D | Goals For | Goals Against |
|-----------------|---------|---|---|---|-----------|---------------|
| Garuda FC       | 0       | 0 | 0 | 0 | 0         | 0             |
| Rajawali United | 0       | 0 | 0 | 0 | 0         | 0             |
| Elang Muda      | 1       | 0 | 0 | 1 | 2         | 2             |
| Meteor Depok    | 1       | 0 | 0 | 1 | 2         | 2             |

---

## Seeder Execution Order

```
1. UserSeeder              → super_admin, admin, auditor, 15 players
2. VenueSeeder             → 8 venues (7 active, 1 inactive)
3. TeamSeeder              → 6 teams + auto TeamStats + TeamVerification (via Observer) + members + schedules
4. VenueScheduleSeeder     → venue booking slots for next 2 weeks
5. TeamVerificationSeeder  → updates verification status (verified/pending/rejected)
6. MatchRequestSeeder      → 6 match requests with various statuses
7. MatchSeeder             → 6 matches + players + costs + team stats for approved match
8. MatchScoreAuditSeeder   → audit records for 2 completed matches
```

---

## Panel Access

| URL             | Accessible By                        |
|-----------------|--------------------------------------|
| /admin          | super_admin, admin, auditor          |
| /admin/...      | Navigation items filtered by role    |

**Admin sees:** Venues, Venue Schedules, Teams, Matches, Match Requests, Statistics

**Auditor sees:** Team Verifications, Match Score Audits only

---

## Resetting the Database

```bash
php artisan migrate:fresh --seed
```

> ⚠️ This permanently deletes all data and re-seeds from scratch.
