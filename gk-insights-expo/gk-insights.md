---
title: Getting Started with GitKraken Insights
description: Learn how to request access, connect your repositories, and configure GitKraken Insights for your organization.
product: GitKraken Insights
content_type: install
audience: admin
plan_required: GitKraken Insights
integrations: [GitHub, GitLab, GitLab Self-Hosted, Bitbucket, Azure DevOps, Jira Cloud, Claude Code, Codex, Cursor, GitHub Copilot]
status: GA
taxonomy:
    category: gk-insights
---
<kbd>Last updated: March 2026</kbd>

GitKraken Insights turns raw Git data into clear, useful metrics for developers and leaders. GitKraken Insights pulls code activity, pull requests, issues, and CI/CD results into a single view that fits directly into existing workflows.

Instead of surface-level stats, GitKraken Insights shows how work connects to team goals and points out ways to improve flow and productivity.

> **Plan:** GitKraken Insights (available by request)
> **Platform:** Browser only via [gitkraken.dev](https://gitkraken.dev)
> **Role:** Lead, Admin, or Owner
> **Integrations:** GitHub, GitLab (cloud and self-hosted), Bitbucket, Azure DevOps, Jira Cloud
> **AI providers:** Claude Code, Codex, Cursor, GitHub Copilot (optional)

<figure>
  <img src="/wp-content/uploads/insights-dashboard-oct-2025.png" srcset="/wp-content/uploads/insights-dashboard-oct-2025@2x.png" class="help-center-img img-bordered" alt="Dashboard view of GitKraken Insights metrics and charts for development activity" />
  <figcaption style="text-align: center; color: #888">Overview of GitKraken Insights</figcaption>
</figure>

---

## Request Access

GitKraken Insights is available by request only. To get started, [request a guided tour](https://www.gitkraken.com/insights#form).  

 A member of the GitKraken team will contact you right away to walk you through GitKraken Insights and explain how to enable access for your organization.

**Note:** GitKraken Insights is a standalone product. [Contact GitKraken](https://www.gitkraken.com/insights#form) to get started.

---

## Connecting your data

Once your access is approved, you can connect GitKraken Insights to your repositories and configure settings for your organization.  

Currently, Insights supports connections with GitHub, GitLab (cloud and self-hosted), Bitbucket, Azure DevOps, and Jira Cloud.

In addition, you can connect AI providers to enable AI Impact insights (like Duplicated Code, Prompt Acceptance Rate, and more). To learn how GitKraken Insights measures AI adoption across your organization, see [AI Adoption](/gk-insights/ai-adoption).
---

### 1. Import your repositories

1. In GitKraken.dev, go to **Insights > Data Connection**.  
2. Click to connect with GitHub, GitLab, GitLab Self-Hosted, Azure DevOps, Claude Code, Codex, Cursor, GitHub Copilot, Bitbucket, or Jira Cloud. 
3. Authorize GitKraken Insights to connect with GitHub.  
4. Select which repositories to track. Use the filter option at the top of the page to quickly narrow down the list.  


<figure>
  <img src="/wp-content/uploads/data-connection-dec-2025.png" srcset="/wp-content/uploads/data-connection-dec-2025@2x.png" class="help-center-img img-bordered" alt="Screenshot of Data Connection page to connect GitHub, GitLab, or Jira for Insights" />
  <figcaption style="text-align: center; color: #888">Connect GitHub, GitLab, or Jira to enable Insights</figcaption>
</figure>

<figure>
  <img src="/wp-content/uploads/authorize-gitclear.png" srcset="/wp-content/uploads/authorize-gitclear@2x.png" class="help-center-img img-bordered" alt="Screenshot authorizing GitHub access for GitKraken Insights" />
  <figcaption style="text-align: center; color: #888">Authorize GitHub access for GitKraken Insights</figcaption>
</figure>

<!-- FLAG FOR HUMAN REVIEW: import-repos.png is not present in the _images/ directory. Verify the correct filename. -->
<figure>
  <img src="/wp-content/uploads/import-repos.png" srcset="/wp-content/uploads/import-repos@2x.png" class="help-center-img img-bordered" alt="Screenshot of repository selection to choose which repositories to import into Insights" />
  <figcaption style="text-align: center; color: #888">Select which repos to import. You can always import more later.</figcaption>
</figure>

#### Avoiding GitHub API rate limits

If you're importing a large number of repositories, depending on size and commit history, you may encounter GitHub's hourly API rate limits. This can temporarily throttle other GitHub services used by your organization.

To avoid this, additional members of your organization can connect to GitKraken Insights using a [Lead role](/gk-dev/gk-dev-organization/#roles). When multiple people are connected, the app distributes processing across their GitHub tokens to help avoid throttling.

After the initial import is complete, rate limit issues are unlikely to recur.


---

### Connect an AI provider (optional)

As of June 2026, GitKraken Insights only supports connections with Claude Code, Codex, Cursor, and GitHub Copilot to enable AI insights.

To enable AI Impact insights, connect your preferred AI provider:
1. In GitKraken.dev, go to [**Insights > Data Connection**](https://gitkraken.dev/insights/data-connections).
2. Click to `Manage` with Claude Code, Codex, Cursor or Github Copilot.
3. In the new window, select the AI provider you wish to connect with and enter the provider Token.
4. Click **Connect AI Provider** to finish the connection.
<figure>
  <img src="/wp-content/uploads/gk-dev-ai-provider-connection@2x.png" class="help-center-img img-bordered" alt="Screenshot of AI provider connection" />
  <figcaption style="text-align: center; color: #888">Connect your AI provider to enable AI Impact insights</figcaption>
</figure>

### 2. Confirm your profile details

After connecting repositories, confirm your personal details:

- First and last name  
- Time zone  
- Job role  

<figure>
  <img src="/wp-content/uploads/set-role-oct-2025.png" srcset="/wp-content/uploads/set-role-oct-2025@2x.png" class="help-center-img img-bordered" alt="Screenshot of profile form to confirm name, time zone, and job role before continuing" />
  <figcaption style="text-align: center; color: #888">Confirm your details before continuing</figcaption>
</figure>


---

### 3. Monitor data import progress

Once setup is complete, GitKraken Insights will begin importing your repository data.  

- Expect **past month’s activity** to appear within a few hours.  
- Full **year’s activity** is usually ready within one to two days.  
- Track import progress anytime from the **Dashboard** tab.  

<figure>
  <img src="/wp-content/uploads/import-progress.png" srcset="/wp-content/uploads/import-progress@2x.png" class="help-center-img img-bordered" alt="Dashboard view showing import progress while Insights processes your repository data" />
  <figcaption style="text-align: center; color: #888">Monitor import progress while Insights processes your data</figcaption>
</figure>