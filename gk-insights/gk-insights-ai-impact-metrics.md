---
title: AI Impact Metrics in GitKraken Insights
description: Learn about AI Impact metrics in GitKraken Insights, including code rework, duplication, suggestion acceptance rates, and active AI tool usage.
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
integrations: [Claude Code, Cursor, GitHub Copilot]
status: GA
taxonomy:
    category: gk-insights
---
<kbd>Last updated: March 2026</kbd>

GitKraken Insights tracks eight AI Impact metrics that measure how AI coding tools affect code quality and developer efficiency. By tracking rework, duplication, and post-PR changes, teams can identify improvements in code and workflow and guide effective use of AI tools.

> **Plan:** GitKraken Insights
> **Platform:** Browser only via [gitkraken.dev](https://gitkraken.dev)
> **Role:** Lead, Admin, or Owner
> **Prerequisite:** Connected AI provider (Claude Code, Cursor, or GitHub Copilot). See [Getting Started](/gk-insights/gk-insights#ai-provider-connection-optional).

| Metric | Group | Definition |
|---|---|---|
| Copy/Paste vs Moved % | Code quality impact | Duplicated vs refactored code over time |
| Duplicated Code | Code quality impact | Lines in duplicate blocks detected |
| Percent of Code Rework | Code quality impact | Recently written code modified again |
| Post PR Work Occurring | Code quality impact | Follow-up work and fixes after merge |
| Active Users | AI adoption | Unique users active in connected AI providers |
| Suggestions | AI adoption | AI-generated suggestions offered (by total lines) |
| Prompt Acceptance Rate | AI adoption | % of prompt suggestions accepted |
| Tab Acceptance Rate | AI adoption | % of tab suggestions accepted |

---

## Copy/Paste vs Moved Percent

**Definition:** _Copy/Paste vs Moved Percent compares how much code is duplicated versus refactored or relocated over time. Moved lines reflect healthy code reorganization, while copy/pasted lines often signal duplicated logic that can lead to technical debt._

Tracking this metric helps teams distinguish between maintainable refactoring and potentially problematic duplication. This is especially important for teams using AI coding assistants, which tend to duplicate code rather than abstract or reuse it, leading to higher long-term maintenance costs if left unchecked.

<figure>
  <img src="/wp-content/uploads/copy-paste-moved.png" srcset="/wp-content/uploads/copy-paste-moved@2x.png" class="help-center-img img-bordered" alt="Line chart comparing duplicated and moved code over time" />
  <figcaption style="text-align: center; color: #888">Compare duplicated versus refactored code over time to identify reuse and duplication trends.</figcaption>
</figure>

You can hover over points on the chart to view the exact percentages for a specific time period, making it easy to see changes before and after implementing an AI coding tool.

---

## Duplicated Code

**Definition:** _The amount of lines in duplicate blocks detected._

Duplicated Code measures redundant code blocks in your codebase. It correlates with increased maintenance costs and a higher risk of defect propagation, since duplicated logic must be updated consistently across multiple places.

When duplication rises, it often signals that AI-assisted or manual coding practices are reusing code without enough refactoring.

<figure>
  <img src="/wp-content/uploads/duplicated-code.png" srcset="/wp-content/uploads/duplicated-code@2x.png" class="help-center-img img-bordered" alt="Chart showing duplicated code trends over time and growth in repeated code patterns" />
  <figcaption style="text-align: center; color: #888">Track how duplicated code changes over time to identify growth in repeated code patterns.</figcaption>
</figure>

The detailed view breaks this down by repository and time period, showing where duplication is concentrated and how it changes alongside overall development activity, such as commits, pull requests, and issues resolved. This helps teams connect code duplication trends to broader workflow patterns and assess the real impact of AI tools on code quality.

<figure>
  <img src="/wp-content/uploads/duplicated-code-details.png" srcset="/wp-content/uploads/duplicated-code-details@2x.png" class="help-center-img img-bordered" alt="Chart view of duplicated code by repository showing which projects contribute most redundancy" />
  <figcaption style="text-align: center; color: #888">View duplicated code by repository to see which projects contribute most to redundancy.</figcaption>
</figure>

---

## Percent of Code Rework (Churned Lines)

**Definition:** _The percentage of recently written code that gets modified again quickly, which may indicate instability or changing requirements._

Percent of Code Rework (Churned Lines) calculates how much recently written code gets modified again, signaling instability, shifting requirements, or potential quality issues. High churn rates can reflect rework caused by unclear goals, rushed reviews, or limitations in AI-assisted code generation.

<figure>
  <img src="/wp-content/uploads/percent-of-code-rework.png" srcset="/wp-content/uploads/percent-of-code-rework@2x.png" class="help-center-img img-bordered" alt="Chart tracking how frequently code is rewritten or replaced over time to show rework trends" />
  <figcaption style="text-align: center; color: #888">Track how frequently code is rewritten or replaced over time to identify rework trends.</figcaption>
</figure>

The detailed view breaks this down across repositories and time periods, helping teams see where rework is concentrated and how it aligns with activity levels like commits, pull requests, and issue resolutions. By monitoring this metric, teams can assess whether AI tools are improving long-term code quality or introducing avoidable rework.

<figure>
  <img src="/wp-content/uploads/duplicated-code-details-view.png" srcset="/wp-content/uploads/duplicated-code-details-view@2x.png" class="help-center-img img-bordered" alt="Chart of rework percentages by repository showing where code churn is most frequent" />
  <figcaption style="text-align: center; color: #888">View rework percentages by repository to pinpoint where churn is most frequent.</figcaption>
</figure>

---

## Post PR Work Occurring

**Definition:** _Follow-up work and bug fixes needed after merging, indicating the initial quality of AI-assisted code._

Post PR Work Occurring quantifies rework and fixes needed after a pull request is merged, providing an early signal of code quality. It is especially useful when evaluating AI-assisted development, where code may pass initial review but still require corrections post-merge.

<figure>
  <img src="/wp-content/uploads/post-pr-work-occuring.png" srcset="/wp-content/uploads/post-pr-work-occuring@2x.png" class="help-center-img img-bordered" alt="Chart tracking post-merge follow-up work over time and spikes in follow-up activity" />
  <figcaption style="text-align: center; color: #888">Track how much post-merge work occurs over time to identify spikes in follow-up activity.</figcaption>
</figure>

The detailed view breaks this activity down by repository and time period, revealing patterns in post-merge changes and how they relate to broader development activity, such as commits and pull requests. Tracking this metric over time helps teams improve review quality and identify whether AI-assisted coding leads to more or less post-merge rework.

<figure>
  <img src="/wp-content/uploads/post-pr-work-occuring-details.png" srcset="/wp-content/uploads/post-pr-work-occuring-details@2x.png" class="help-center-img img-bordered" alt="Detailed chart of post-merge work by repository where additional changes concentrate" />
  <figcaption style="text-align: center; color: #888">View post-merge work by repository to see where additional changes are concentrated.</figcaption>
</figure>

---

## Active Users

**Definition:** _The total count of unique users active in the connected AI provider integrations._

Active Users counts developers actively using AI coding assistants from connected providers. It establishes a baseline for tracking AI adoption across your teams and supports analysis of AI tool investment impact and developer productivity trends.

<figure>
  <img src="/wp-content/uploads/active-users.png" srcset="/wp-content/uploads/active-users@2x.png" class="help-center-img img-bordered" alt="Chart showing how many developers actively use AI coding tools over time" />
  <figcaption style="text-align: center; color: #888">See how many developers are actively using AI coding tools over time.</figcaption>
</figure>

---

## Suggestions (by total lines)

**Definition:** _The number of suggestions offered from your connected AI provider integrations._

Suggestions (by total lines) tracks the volume of AI-generated code suggestions offered to developers. It provides scale context for measuring the extent of AI contribution to overall development output and helps teams evaluate how heavily AI is being leveraged in the coding process.

<figure>
  <img src="/wp-content/uploads/suggestions.png" srcset="/wp-content/uploads/suggestions@2x.png" class="help-center-img img-bordered" alt="Chart tracking volume of AI-generated suggestions and their influence on development activity" />
  <figcaption style="text-align: center; color: #888">Track the number of AI-generated suggestions to understand their influence on development activity.</figcaption>
</figure>

---

## Prompt Acceptance Rate

**Definition:** _The percentage of prompt results a developer accepted from your connected AI provider integrations._

Prompt Acceptance Rate measures how many AI-generated code suggestions are accepted by developers. A higher rate indicates stronger alignment between AI outputs and developer expectations, signaling trust and effectiveness in AI-assisted workflows.

<figure>
  <img src="/wp-content/uploads/prompt-acceptance.png" srcset="/wp-content/uploads/prompt-acceptance@2x.png" class="help-center-img img-bordered" alt="Chart visualizing how often developers accept AI prompt suggestions over time" />
  <figcaption style="text-align: center; color: #888">Visualize how often AI suggestions are accepted by developers over time.</figcaption>
</figure>

---

## Tab Acceptance Rate

**Definition:** _Measures what percentage of AI-generated code suggestions developers accept with tabs._

Like prompt acceptance, this metric reflects the effectiveness and usability of AI suggestions. A higher tab acceptance rate indicates that developers find the suggestions useful and frictionless to apply, helping gauge the seamlessness of AI integration in development workflows.

<figure>
  <img src="/wp-content/uploads/tab-acceptance.png" srcset="/wp-content/uploads/tab-acceptance@2x.png" class="help-center-img img-bordered" alt="Chart tracking how frequently developers accept AI suggestions via tab completion" />
  <figcaption style="text-align: center; color: #888">Track how frequently AI suggestions are accepted via tab completion.</figcaption>
</figure>
