# Task Manager - Fullstack Developer Assessment

A simple task management application built with Laravel 12 and Vue 3.

## Tech Stack

- Laravel 12 + Sanctum (API authentication)
- Vue 3 + Vue Router + Axios
- Tailwind CSS v4
- MySQL 8
- Docker

## Quick Start

```bash
git clone <repo-url>
cd assessment-laravel-vue
chmod +x setup.sh
./setup.sh
```

The app will be available at http://localhost:8000

**Test credentials:**
- Email: candidate@example.com
- Password: password

## Output

When you're done, send the result of your process to `hiring@aiapply.co` and include the following:

1. **GitHub repo** — fork this repo, push your work to a new branch, and include the repo link
2. **Video / screen recording** — upload to YouTube (unlisted) or Google Drive and include the link

Your email should include both the GitHub repo and the video.

## Assessment Instructions

### Format

- **Duration:** 2 hours
- **Recording:** Record your screen from start to finish (OBS, Loom, or any tool)
- **Think aloud:** Ideally, talk through your thinking process out loud while you work — what you plan to do, why you make certain decisions, what you search for. This helps us understand how you approach problems.
- **AI tools:** Use any AI tools you want (Cursor, Copilot, ChatGPT, Claude) — this is encouraged!
- **Commits:** Commit frequently, at least every 15 minutes
- **Completion:** You do NOT need to finish everything. We evaluate your process, not just the result. Use the time wisely.

### Tasks

Complete in order. Prioritize quality over quantity.

#### 🟢 Task 1: Filtering & Search (Easy)

Add the ability to filter and search tasks on the task list page.

**Done when:**

- User can filter tasks by status (All / To Do / In Progress / Done)
- User can search tasks by name
- Filters work server-side, not just in the browser
- Status filter and search work together

#### 🟡 Task 2: Comments Feature (Medium)

Add a comments system to tasks.

**Done when:**

- Users can leave comments on a task
- Comments are visible on the task detail page with author and timestamp
- Comment body is validated (required, reasonable max length)
- Validation errors are shown to the user
- Full stack: database, backend, and frontend all implemented

#### 🔴 Task 3: Real-time Notifications (Hard)

Add real-time notifications when someone comments on a task.

**Done when:**

- When a comment is added, other users see a notification without refreshing the page
- The new comment appears automatically in the comment list
- The notification is visible and informative (who commented, on which task)

#### 🌟 Bonus: Activity Log

If you finish the tasks above and still have time, add an activity log to tasks.

**Done when:**

- Important events are logged automatically (task created, updated, deleted, comment added)
- The task detail page shows a timeline/history of what happened
- The log captures who did what and when

This is NOT expected — it's a bonus for candidates who move fast. Don't attempt it before completing the main tasks.

### What We Evaluate

1. **Planning & approach** — How you start, do you read code first?
2. **AI tool usage** — How effectively you use AI, quality of prompts, do you verify output?
3. **Code quality** — Structure, naming, edge cases, validation
4. **Speed & prioritization** — How far you got, smart tradeoffs
5. **Communication** — Think aloud clarity, explaining decisions
6. **Problem-solving** — How you handle blockers and debugging

Good luck! 🚀
