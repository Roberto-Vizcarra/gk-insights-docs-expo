---
title: Pull Request Metrics in GitKraken Insights
description: Learn about Pull Request metrics in GitKraken Insights, including cycle time, lead time, review activity, and code review hours.
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
integrations: [GitHub, GitLab, GitLab Self-Hosted, Bitbucket]
status: GA
taxonomy:
    category: gk-insights
---
<kbd>Last updated: March 2026</kbd>

GitKraken Insights tracks ten Pull Request metrics that measure how code changes move through review and deployment. These metrics highlight slowdowns, surface patterns in reviews, and uncover blockers that may affect delivery. Pull Request data comes from connected GitHub, GitLab (cloud or self-hosted), or Bitbucket repositories.

> **Plan:** GitKraken Insights
> **Platform:** Browser only via [gitkraken.dev](https://gitkraken.dev)
> **Role:** Lead, Admin, or Owner
> **Prerequisite:** Connected repositories with pull request data (GitHub, GitLab cloud or self-hosted, or Bitbucket)

| Metric | Measures | Unit | Rolling Window |
|---|---|---|---|
| First Response Time | PR opened to first review/comment | Hours | 7-day avg |
| Cycle Time | First commit to PR merge | Days | 7-day avg |
| Lead Time | First commit to deployed | Days | 7-day avg |
| Number of Reviews | Review volume per period | Count | 7-day |
| Open Time | PR opened to merged | Days | N/A |
| PRs Abandoned | PRs closed without merge | Count | N/A |
| PRs Merged Without Review | PRs merged with no review | Count | N/A |
| PR Comments | Total comments on PRs | Count | N/A |
| PR Size / Effort | Aggregate diff delta of merged PRs | Diff delta | N/A |
| Code Review Hours | Avg review time per committer | Hours | N/A |

> **Cycle Time vs Open Time vs Lead Time:** Cycle Time starts at the first commit; Open Time starts when the PR is opened; Lead Time extends through deployment. Use Cycle Time for overall delivery speed, Open Time for review bottlenecks, and Lead Time for the full development-to-production pipeline.  
> **First Response Time** helps identify reviewer engagement gaps. **PRs Abandoned** and **PRs Merged Without Review** signal process health. **PR Size/Effort** and **Code Review Hours** help balance reviewer workload.

---

## First Response Time ("Pickup Time")

**Definition:** _The time measured from when a PR is opened to when the 1st review or comment is left on a PR._

First Response Time shows how long each pull request within a selected timeframe took to have a first response (comment or review). Values are expressed in **hours** and averaged over a **7-day period**. Shorter pickup times indicate faster reviewer engagement and healthier collaboration.

<figure>
  <img src="/wp-content/uploads/first-response-time.png" srcset="/wp-content/uploads/first-response-time@2x.png" class="help-center-img img-bordered" alt="Chart showing average time for a pull request to receive its first comment or review" />
  <figcaption style="text-align: center; color: #888">Average time it took for PR to receive comment or review.</figcaption>
</figure>

---

## Cycle Time ("first commit" to "merge")

**Definition:** _The time measured between the 1st commit of a PR to when the PR is merged._

Cycle Time shows how long each pull request within a selected timeframe took to merge from the time the first commit was made. Values are expressed in **days** and averaged over a **7-day period**. Cycle time provides insight into overall delivery speed, highlighting how quickly work moves from coding to production.

<figure>
  <img src="/wp-content/uploads/cycle-time-line.png" srcset="/wp-content/uploads/cycle-time-line@2x.png" class="help-center-img img-bordered" alt="Chart showing average time from first commit to pull request merge" />
  <figcaption style="text-align: center; color: #888">Average time from first commit to PR merge.</figcaption>
</figure>

The **Details** view offers deeper analysis.

<figure>
  <img src="/wp-content/uploads/pr-cycle-time-details.png" srcset="/wp-content/uploads/pr-cycle-time-details@2x.png" class="help-center-img img-bordered" alt="Detailed breakdown chart of pull request cycle time with scatter plot and categories" />
  <figcaption style="text-align: center; color: #888">Get a detailed breakdown of PR cycle time.</figcaption>
</figure>

- Pull requests are grouped into four categories by duration:
  - **Elite:** Less than 1 day
  - **Fast:** 1–7 days
  - **Average:** 7–29 days
  - **Slower:** Over 30 days
- Each node in the scatter plot is interactive, showing PR details such as time since merge, PR name, author, and a link to open directly in GitHub.
- A sortable table lists all PRs below the chart. You can sort by **Days**, **Pull Request name**, **Author**, **Date Opened**, or **Date Merged**.

---

## Lead Time

**Definition:** _The time from the first commit in a pull request to when that code is deployed to production. This bridges development and deployment activities._

Lead Time shows how long each pull request within a selected timeframe remained open, measured from when the PR was created until it was merged. Values are expressed in **days** and averaged over a **7-day period**.

<figure>
  <img src="/wp-content/uploads/lead-time.png" srcset="/wp-content/uploads/lead-time@2x.png" class="help-center-img img-bordered" alt="Chart showing how long pull requests remained open until they were merged" />
  <figcaption style="text-align: center; color: #888">See how long PRs remained open until it was merged.</figcaption>
</figure>

---

## Number of Reviews per Day/Week/Month

**Definition:** _The volume of code reviews being conducted, indicating team review capacity and activity._

Number of Reviews shows the total number of reviews (all types) completed over a given period of time. Values are expressed in **reviews** and averaged over a **7-day window**. Tracking review activity helps teams understand collaboration patterns and reviewer workload across different timeframes (daily, weekly, or monthly).

<figure>
  <img src="/wp-content/uploads/number-of-reviews.png" srcset="/wp-content/uploads/number-of-reviews@2x.png" class="help-center-img img-bordered" alt="Chart showing number of reviews completed over daily, weekly, or monthly periods" />
  <figcaption style="text-align: center; color: #888">See how many reviews have been given over a time period.</figcaption>
</figure>

---

## Open Time ("opened" to "merged")

**Definition:** _The time between when a pull request is opened and when it is merged._

Open Time captures the duration from pull request creation to merge. It differs from PR Cycle Time, which starts from the first commit. Open Time isolates the review and approval phase, helping teams identify delays specifically related to code review and collaboration workflows.

<figure>
  <img src="/wp-content/uploads/open-time.png" srcset="/wp-content/uploads/open-time@2x.png" class="help-center-img img-bordered" alt="Chart showing how long each pull request remained open before being merged" />
  <figcaption style="text-align: center; color: #888">Shows the time each pull request remained open before being merged.</figcaption>
</figure>

The **Open Time details view** provides a stacked bar chart that visualizes average PR open durations per repository across time intervals. Each bar segment represents a specific repository, allowing teams to compare performance across services.

<figure>
  <img src="/wp-content/uploads/open-time-details.png" srcset="/wp-content/uploads/open-time-details@2x.png" class="help-center-img img-bordered" alt="Stacked bar chart of average open durations per repository across time intervals" />
  <figcaption style="text-align: center; color: #888">Detailed breakdown of PR open durations across repositories and time intervals.</figcaption>
</figure>

Key highlights include:

- **Hover insights**: Hovering over bars shows per-repository open time for the selected week.
- **Repository comparison**: See how review duration varies across services.
- **Summary metrics**:
  - Pull requests merged
  - Pull request cycle time
  - Pull request average diff delta
  - Pull requests opened

These metrics help teams identify trends, uncover review bottlenecks, and monitor efficiency over time.

---

## Number of PRs Abandoned

**Definition:** _Number of pull requests closed without being merged._

PRs Abandoned can reveal wasted engineering effort, scope management issues, or breakdowns in work planning and prioritization. A high abandonment rate may indicate unclear requirements, excessive rework, or bottlenecks earlier in the development process.

<figure>
  <img src="/wp-content/uploads/prs-abandoned.png" srcset="/wp-content/uploads/prs-abandoned@2x.png" class="help-center-img img-bordered" alt="Chart of pull requests closed without merge, segmented by repository and time" />
  <figcaption style="text-align: center; color: #888">Visualizes how many PRs were closed without merging, segmented by repository and time.</figcaption>
</figure>

---

## Number of PRs Merged Without Review

**Definition:** _The number of pull requests that have been merged without any (bot or human) review._

PRs Merged Without Review can indicate potential process gaps in peer review enforcement. It identifies PRs integrated without peer review, exposing quality risks and gaps in your code review governance that could lead to production defects.

<figure>
  <img src="/wp-content/uploads/prs-merged-without-review.png" srcset="/wp-content/uploads/prs-merged-without-review@2x.png" class="help-center-img img-bordered" alt="Chart tracking pull requests merged without peer or bot review over time" />
  <figcaption style="text-align: center; color: #888">Tracks PRs merged without review, highlighting enforcement issues in your review process.</figcaption>
</figure>

---

## Number of PR Comments

**Definition:** _The total number of comments left on your pull requests._

Number of PR Comments measures the level of engagement during code reviews, revealing how thoroughly code is being evaluated. A high number of comments can indicate active feedback, knowledge sharing, and mentorship opportunities across the team.

<figure>
  <img src="/wp-content/uploads/pr-comments.png" srcset="/wp-content/uploads/pr-comments@2x.png" class="help-center-img img-bordered" alt="Chart showing total pull request comments reflecting review depth and collaboration intensity" />
  <figcaption style="text-align: center; color: #888">Shows total PR comments, which can reflect review depth and collaboration intensity.</figcaption>
</figure>

---

## PR Size / Effort

**Definition:** The aggregate amount of change (diff delta) across all pull requests that were merged during a given time period.

PR Size / Effort reflects the total effort involved in the review process. It is calculated by summing the "Before PR submitted" and "While under review" diff delta values, representing the total size of the pull request and the energy required for its review.

<figure>
  <img src="/wp-content/uploads/pr-size.png" srcset="/wp-content/uploads/pr-size@2x.png" class="help-center-img img-bordered" alt="Chart displaying total pull request diff delta size and review workload trends" />
  <figcaption style="text-align: center; color: #888">Displays total PR size (diff delta), helping evaluate review load and PR size trends.</figcaption>
</figure>

By aggregating the total change volume across merged PRs, this helps teams balance workload distribution and recognize when PRs may be too large for effective review.

---

## Code Review Hours

**Definition:** Average time spent in hours during the "review period" of your team's pull requests.

**How it's calculated:** Total review hours / number of committers.

Code Review Hours quantifies the average time investment in reviewing code. It helps leaders evaluate whether code review capacity aligns with delivery goals and whether review processes need optimization to support team velocity and quality.

<figure>
  <img src="/wp-content/uploads/code-review-hours.png" srcset="/wp-content/uploads/code-review-hours@2x.png" class="help-center-img img-bordered" alt="Chart showing average code review hours per developer and team review investment trends" />
  <figcaption style="text-align: center; color: #888">Average code review time per developer, highlighting team review investment trends.</figcaption>
</figure>
