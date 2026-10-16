---
title: Surveys
description: Send developer surveys from the GitKraken Insights survey library, track responses, and read and export the results.
product: GitKraken Insights
content_type: how-to
audience: team-lead
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: purple
    card_description: Send developer surveys and read the results
    card_icon: clipboard-list
    nav_category: getting-started
    nav_label: Surveys
    nav_order: 80
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

The **Surveys** page lets you ask your developers how their work and their AI tools are going, and read the answers next to the numbers. Use it to send a survey from the library of ready-made surveys, track who has responded, and read and export the results. The metrics show what developers do; a survey shows how they feel about it. This page doesn't cover writing your own questions: every survey starts from a library template.

> **Who can see it:** The Surveys page is available to **Owners**, **Admins**, and **Managers**. The people you send a survey to answer it from the link in their email.

---

## Open Surveys

In the Insights sidebar, under **Surveys**, click **Surveys**. The page has two tabs:

* **Survey Library** — the ready-made surveys you can send. The page opens on this tab.
* **Survey Results** — the surveys your organization has sent, with their responses.

Both tabs have category filters, such as **AI Adoption**, **Satisfaction & Sentiment**, and **Workflows and Processes**. Click **All Surveys** to show every category again.

## Send a survey

Each survey in the **Survey Library** shows its name, a short description, its category, the number of questions, and an estimate of how long it takes to answer.

To send a survey:

1. On the **Survey Library** tab, find the survey and click **Send survey**. To read the questions first, click **Preview survey**.
2. Optional: to rename the survey for this send, click the pencil next to its title, type the new title, and save it.
3. Under **Shared with**, choose the teams and users who should receive the survey. Use **Select all teams** or **Select all users** to add everyone in one step. The counts beside **Teams** and **Users** show how many of each you've chosen.
4. Under **Delivery Method**, choose **Send now** to send the survey immediately, or **Schedule for later** to pick a **Send date** and **Send time**.
5. Under **Stop accepting responses**, choose **Never**, or **On** and a date. The end date defaults to 14 days after the send, and can't be before the send date.
6. Click **Send survey**, or **Schedule survey** if you scheduled it.

Recipients receive the survey by email. When the survey goes out, a confirmation offers **Copy link**, so you can also share the survey link yourself.

## Track a survey's responses

The **Survey Results** tab lists every survey your organization has sent, with these columns:

* **Survey name**
* **Status** — **Scheduled**, **Active**, **Completed**, or **Canceled**. Hover the status for the dates the survey accepts responses.
* **Recipients** — how many people received the survey.
* **Response rate** — the share of recipients who answered.
* **Schedule** — when the survey is sent.
* **Sent on** — the date the survey went out.
* **Actions** — **Open results**, with a menu of more actions.

<figure>
  <img src="/wp-content/uploads/gki-surveys-results-list-01-v2@2x.png" class="help-center-img img-bordered" alt="The Survey Results tab, beside Survey Library, on the Surveys page in GitKraken Insights, with All Surveys, AI Adoption, Satisfaction & Sentiment and Workflows and Processes filters and a table of three surveys, Code Review Process, AI Tools Adoption and Developer Experience Pulse, showing Status, Recipients, Response rate, Schedule, Sent on, and an Open results action for each" />
  <figcaption>The Survey Results tab on the Surveys page.</figcaption>
</figure>

The actions menu beside **Open results** offers these actions:

* **Copy link** — copies the survey link to share it yourself.
* **Send reminder** — reminds recipients to answer. Available while the survey is active.
* **Delete survey** — deletes the survey and its results. Available to the person who sent the survey and to organization owners and admins.

## Read a survey's results

Click **Open results** to see a survey's results. Four tiles sit at the top:

* **Overall score** — the average score across the survey's rated questions.
* **Response rate** — the share of recipients who answered.
* **Response rate trend** and **Average score** — these read **N/A** for a survey sent once.

Below the tiles, each question has its own card. A rated question shows how many people answered, the average score, and the share of answers at each point from 1 to 5. A choice question shows the share of answers for each choice. A question with written answers lists the responses.

<figure>
  <img src="/wp-content/uploads/gki-survey-results-one-off-01-v2@2x.png" class="help-center-img img-bordered" alt="Results of the AI Tools Adoption survey in GitKraken Insights, with tiles for Overall score 3.9, Response rate 80%, Response rate trend and Average score, an Export menu, and four question cards each showing 8 responses, the question's average and the share of 1 to 5 answers" />
  <figcaption>The results of a survey, one card per question.</figcaption>
</figure>

## Export survey results

On a survey's results, open **Export** and choose a format:

* **Export as CSV (summary)**
* **Export as CSV (chart-friendly)**
* **Export as JSON**
* **Print / Save as PDF**

## Related pages

| Page | What it covers |
| --- | --- |
| [Getting Started](/insights-expo/expo-ai-adoption-getting-started) | Orientation to GitKraken Insights |
| [For Team Leads](/insights-expo/expo-ai-adoption-for-team-leads) | Weekly rituals where survey results fit alongside the metrics |
