---
title: AI Adoption Settings in GitKraken Insights
description: Reference for the admin settings that affect scores and metrics in GitKraken Insights, including Maturity Factor, Tier Weights, Provider Weights, Output Score weights and exclusion rules, Cycle Time Max Days, AI-to-Commit Correlation Window, Developer Hourly Rate, Working Hours Per Week, Baseline Period, AI cost rates, and work categories.
product: GitKraken Insights
content_type: reference
audience: admin
plan_required: GitKraken Insights
integrations: [Claude Code, Codex, Cursor]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: red
    card_description: Configuration reference for all AI Adoption settings
    card_icon: adjustments
    nav_category: admin
    nav_label: Settings
    nav_order: 10
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

Every admin-configurable setting that affects scores and metrics in GitKraken Insights, organized by where it lives in the product, plus a one-line "what it changes."

This page covers the settings that change how scores and metrics are calculated. For product-wide administration — loading the developer roster, defining teams, wiring up Jira for CFR, syncing BambooHR PTO, and Demo Mode — see the [For Admins](/insights-expo/expo-ai-adoption-for-admins) section of the Getting Started guide.

If you came from a metric page to look up a specific setting, jump to the anchor of the same name.

**Where these settings live.** Most of the per-org settings on this page are editable in the Settings → General form and stored in the `app_settings` table. The form is grouped into **Scoring baseline** (`maturity_factor`, `developer_hourly_rate`, Working Hours Per Week, `baseline_period_start`, Cycle Time Max Days, AI Correlation Window, and Ignore reviews from GitHub bot accounts), **Benchmarks**, **Defaults** (`default_department`), **Output score** (Merged PR Weight, Direct Commit Weight, Review Weight, Exclude Chore from Output Score, and Exclusion rules), **Access to Insights**, **Tier and provider weights**, and **AI cost rates**. Two settings live on other Settings tabs: per-developer hourly rates (Settings → Developers) and work categories (Settings → Categorization).

**Who can change them.** Changing these settings requires permission to manage organization settings (organization owners and admins have it). Without it, you see the form read-only, with a note to ask an organization admin or owner. Settings in the form take effect when you click **Save changes**; exclusion rules and AI cost rates save immediately.

---

<figure>
  <img src="/wp-content/uploads/ai-adoption-settings-general.png" class="help-center-img img-bordered" alt="Settings General tab in GitKraken Insights showing Company AI Readiness, Developer Hourly Rate, Baseline Period, and Default Department fields" />
  <figcaption>Settings → General — Maturity Factor (Company AI Readiness %), Developer Hourly Rate, Baseline Period, and Default Department.</figcaption>
</figure>

## General settings

The single most impactful set of knobs. These shift how scores are calculated for every developer in your org.

### Maturity Factor

Labeled **"Company AI Readiness (%)"** in the General tab, under **Scoring baseline**. _In Settings UI: yes._

|  |  |
| --- | --- |
| **Default** | 100% (a factor of 1.00) |
| **Range** | 1 – 100% |
| **Type** | Whole-number percentage |

**What it does.** Scales every P90-based score (Adoption, Agentic, Output Norm). The value is the maximum possible score: a "perfect" developer scores this percentage, not 100. At the default 100%, a developer at the org's 90th percentile scores 100. At 75%, the same developer scores 75 — leaving headroom for the org to grow into the Power User band. Enter at least 1%: a value below 1% isn't valid, and scores fall back to the 100% default.

**Why you'd raise or lower it.** - **Lower (50% – 70%):** Early in your AI rollout. You want every developer to feel like there's runway. Few or no Power Users yet. - **Middle (around 75%):** Active rollout. The tier ceiling pushes the top 10% to keep stretching. - **Higher (85% – 100%, the default):** Mature orgs with widespread, deep adoption where you want the scoring to reflect that maturity in absolute terms. Increase the setting as your org's AI maturity grows.

**Affects:** [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score), [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score), [Output Norm](/insights-expo/expo-ai-adoption-output-output-score), [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier).

→ Full section: [Maturity Factor](/insights-expo/expo-ai-adoption-agentic-maturity-factor)

---

### Tier Weights

Three weights that say how much Adoption, Agentic, and Output each count toward the [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier) composite. _In Settings UI: yes._

| Weight | Default | App-settings key | What it emphasizes |
| --- | --- | --- | --- |
| **Adoption** | 0.5 | `tier_weight_adoption` | Daily, consistent AI use |
| **Agentic** | 0.2 | `tier_weight_agentic` | Autonomous multi-step AI work |
| **Output** | 0.3 | `tier_weight_output` | Effort-weighted shipping rate |

**Range:** Adoption and Agentic (labeled **Autonomy** in Settings) accept 0 or more; Output must be greater than 0. We store raw values, then renormalize to sum to 1.0 on read — the form shows a live preview of the resulting percentages.

**What it does.** The three weights compete for the 100% budget. The tier-badge tooltip in the app shows the live percentages for every developer.

**How to think about tuning.** - Adoption-heavy (e.g. 0.7 / 0.1 / 0.2): "We care that everyone is _trying_ AI. Output will follow." - Output-heavy (e.g. 0.3 / 0.1 / 0.6): "We've moved past the rollout phase — now we care about delivery." - Balanced (the default): A signal that AI use without output is incomplete, and output without AI is the old way.

**Guardrails.** Output must be greater than 0 — the Settings form rejects 0, negatives, and non-numbers for it, so the composite is never empty. Adoption and Autonomy can be 0; setting both to 0 bases tiers on Output alone.

**Affects:** [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier) — and through it, the developer table sorting, the Top 10 widget, the executive ranking, and every breakdown chart that buckets by AI Tier.

→ See also: [Playbook — Set tier weights for your org's maturity](/insights-expo/expo-ai-adoption-playbook-tier-weights)

---

### Provider Weights

_In Settings UI: yes._ Settings → General, under **Tier and provider weights**.

|  |  |
| --- | --- |
| **Default** | 1.0 for every provider |
| **Range** | 0.0 – 1.0 |
| **Type** | Float, one per AI provider |

**What it does.** When your org connects more than one AI provider, Provider Weights control how much each provider counts toward a developer's **Agent Adoption Score** and **Agent Autonomy Score**. One weight per provider applies to both scores. At 1.0 a provider counts at full weight; lower it to de-emphasize a provider; 0 turns off that provider's contribution.

**Which providers are listed.** The form shows one row for each of **Claude**, **Codex**, **Cursor**, **Copilot**, **Devin**, and **OpenCode**, whether or not your org has connected that provider. A provider with no activity contributes nothing, whatever its weight. The [Devin](/insights-expo/expo-ai-adoption-connect-ai-tools#devin) and [OpenCode](/insights-expo/expo-ai-adoption-connect-otel#opencode) connections are available on request — contact your GitKraken account team to have them turned on.

**How they combine.** Each provider is scored on its own; the provider scores are multiplied by their weights, added together, and capped at 100 before the Maturity Factor applies. Provider Weights replace the earlier fixed 25% Cursor Boost — at the default 1.0, Cursor counts at the same weight as every other provider.

**Not the same as the four-factor blend.** Provider Weights decide how providers combine _with each other_. The four-factor blend that produces each provider's own Adoption score — Daily Regularity 35%, Hourly Spread 25%, Prompt Depth 25%, Token Output 15% — is fixed and isn't editable in Settings.

**Affects:** [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score), [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score), and through them [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier).

→ Full section: [Provider Weights](/insights-expo/expo-ai-adoption-agentic-cursor-boost)

---

### Benchmarks

_In Settings UI: yes._ Settings → General, under **Benchmarks**.

|  |  |
| --- | --- |
| **Default** | 0 for every target (follow the org P90) |
| **Range** | 0 or more (Active Hours / Weekday Target: 0 – 24) |
| **Type** | Number, one per target |

**What it does.** Each target is a fixed benchmark that replaces the org P90 as the denominator for one scoring factor. A developer at or above the target gets full credit for that factor. Leave a target at 0 to keep measuring against the org P90. Next to each field, the form shows your org's current P50 and P90 (recommended) as guidance.

| Target | Replaces the org P90 for |
| --- | --- |
| **Active Hours / Weekday Target** | Hourly Spread in the [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score) |
| **Prompts / Weekday Target** | Prompt Depth in the Agent Adoption Score |
| **Tokens / Weekday Target** | Token Output in the Agent Adoption Score |
| **Agentic Intensity Target (per week)** | Tool executions in agentic sessions, in the [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score) |
| **Output Rate Target (per week)** | Output effort points per working week, in [Output Norm](/insights-expo/expo-ai-adoption-output-output-score) |

**When to set one.** Use a target when you want a stable bar that doesn't move as your cohort changes — the org P90 rises and falls with who is active in the window; a target doesn't.

**Affects:** [Agent Adoption Score](/insights-expo/expo-ai-adoption-agentic-adoption-score), [Agent Autonomy Score](/insights-expo/expo-ai-adoption-agentic-autonomy-score), [Output Norm](/insights-expo/expo-ai-adoption-output-output-score), and through them [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier).

---

### Merged PR Weight

_In Settings UI: yes._ Settings → General, under **Output score**.

|  |  |
| --- | --- |
| **Default** | 1 |
| **Range** | 0.0 – 1.0 |
| **Type** | Float |

**What it does.** Scales the effort from PRs the developer authored and merged in the [Output Score](/insights-expo/expo-ai-adoption-output-output-score) formula:

```
Output Score = MergedPRWeight × SUM(PR effort) + DirectCommitWeight × SUM(DC effort) + ReviewWeight × SUM(reviewed-PR effort)
```

* **0.0** — authored PRs are ignored; the score is built from direct commits and reviews only
* **1.0** (default) — authored PRs count at their full effort

**Affects:** [Output Score](/insights-expo/expo-ai-adoption-output-output-score), [Output Norm](/insights-expo/expo-ai-adoption-output-output-score), [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier).

---

### Direct Commit Weight

_In Settings UI: yes._ App-settings key: `direct_commit_weight`.

|  |  |
| --- | --- |
| **Default** | 0.5 |
| **Range** | 0.0 – 1.0 |
| **Type** | Float |

**What it does.** Scales direct commits (pushes straight to a default branch with no PR) relative to merged PRs in the [Output Score](/insights-expo/expo-ai-adoption-output-output-score) formula:

```
Output Score = MergedPRWeight × SUM(PR effort) + DirectCommitWeight × SUM(DC effort) + ReviewWeight × SUM(reviewed-PR effort)
```

* **0.0** — direct commits are ignored entirely
* **0.5** (default) — a direct commit counts half as much as a PR of equivalent effort (at the default Merged PR Weight of 1)
* **1.0** — direct commits count at their full effort

**When to change it.** - Lower toward 0 if your team uses direct commits primarily for trivial maintenance and you don't want them inflating Output Score. - Raise toward 1 if your team uses direct commits for substantive work (e.g. a Trunk-Based Development workflow).

**Affects:** [Output Score](/insights-expo/expo-ai-adoption-output-output-score), [Output Norm](/insights-expo/expo-ai-adoption-output-output-score), [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier).

---

### Review Weight

_In Settings UI: yes._ App-settings key: `review_weight`.

|  |  |
| --- | --- |
| **Default** | 0.5 |
| **Range** | 0.0 – 1.0 |
| **Type** | Float |

**What it does.** Scales review-credit — effort from PRs a developer formally reviewed (state `APPROVED` or `CHANGES_REQUESTED`) — relative to authored PRs in the [Output Score](/insights-expo/expo-ai-adoption-output-output-score) formula:

```
Output Score = MergedPRWeight × SUM(PR effort) + DirectCommitWeight × SUM(DC effort) + ReviewWeight × SUM(reviewed-PR effort)
```

* **0.0** — review work is ignored entirely (the review-credit term disappears from the score)
* **0.5** (default) — a reviewed PR counts half its effort
* **1.0** — a reviewed PR counts its full effort

**When to change it.** - Lower toward 0 if you don't want review activity contributing to Output Score. - Raise toward 1 if review is a first-class deliverable on your team and you want senior reviewers' work reflected at full weight.

**Affects:** [Output Score](/insights-expo/expo-ai-adoption-output-output-score), [Output Norm](/insights-expo/expo-ai-adoption-output-output-score), [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier).

---

### Exclude Chore from Output Score

_In Settings UI: yes._ App-settings key: `output_score_exclude_chore`.

|  |  |
| --- | --- |
| **Default** | On (chores excluded) |
| **Type** | Boolean toggle |

**What it does.** When **on**, Chore-category PRs and commits are subtracted from the _effort_ side of the Output Score. The displayed PR / DC _counts_ still include chores — only the effort sums change.

Which categories count as Chore is set by the **Chore** flag in [Work categories](#work-categories).

**Why the asymmetric treatment.** If a developer's window happens to be all chores (e.g. they spent a sprint on dependency bumps), we don't want them to disappear from the developer table. The Output Score will dip, but the count cell still says "5 PRs, 12 direct commits" so they remain visible. The Output Score tooltip shows you the chore subset that was subtracted.

**When to turn off.** Rarely. The default reflects most orgs' definition of "real output". Turn off if you've explicitly decided chores are part of how you measure delivery — e.g. for an SRE team where dependency upgrades _are_ the job.

**Affects:** [Output Score](/insights-expo/expo-ai-adoption-output-output-score), and downstream [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier).

---

### Exclusion rules

_In Settings UI: yes._ Settings → General, under **Output score**.

|  |  |
| --- | --- |
| **Default** | No rules |
| **Type** | List of rules, each with an on/off toggle |

**What it does.** Leaves matching pull requests out of the [Output Score](/insights-expo/expo-ai-adoption-output-output-score) — for example, release merges into `master`. A matching PR adds no effort to Output Score and is left out of the org benchmark, and its reviewers get no review credit for it. It still counts toward its author's merged pull requests, and metrics outside Output Score are unaffected.

**How to add a rule.** Click **Add rule**, then fill in:

* **Name** — up to 100 characters (for example, "Release merges").
* **Repositories** — **All repositories**, or **Selected repositories** to pick specific ones.
* **Target branch** — the branch the PR merges into (for example, `master`).
* **Source branch** — the branch the PR comes from (for example, `release/*`).

Enter a target branch, a source branch, or both; a branch left empty matches any branch. Use `*` to match any characters. As you fill in the rule, the form shows which pull requests it matches. Click **Save rule**.

**Managing rules.** The rules table lists each rule's name, source → target branches, repositories, and who last updated it. Use the **On** toggle to turn a rule off without deleting it, and **Edit** or **Delete** to change or remove it. Changes save immediately — no **Save changes** click needed — and apply to every time period, not just new activity. A rule limited to selected repositories whose repositories have all been removed matches nothing; open **Edit** and pick repositories to use it again.

**Affects:** [Output Score](/insights-expo/expo-ai-adoption-output-output-score), [Output Norm](/insights-expo/expo-ai-adoption-output-output-score), and downstream [AI Tier](/insights-expo/expo-ai-adoption-agentic-ai-tier).

---

### AI-to-Commit Correlation Window

_In Settings UI: yes._

|  |  |
| --- | --- |
| **Default** | 30 minutes |
| **Range** | 1 – 60 minutes |
| **Type** | Whole number (minutes) |

Labeled **"AI Correlation Window (minutes)"** in the General tab, under **Scoring baseline**.

**What it does.** Sets how close in time a developer's AI activity has to be to a change for that change to count as AI-assisted. A PR or direct commit is flagged AI-assisted when the developer had AI events within this window of the change's lifecycle. A wider window links more activity to AI; a narrower one is stricter. Co-author-trailer detection is separate and isn't affected by this setting.

**Why you'd raise or lower it.** - **Narrower (under 30 minutes):** Your team finds the default over-attributes — developers who happened to have an AI session open get credited for changes they wrote without AI. - **Default (30 minutes):** A practical middle ground for most teams. - **Wider (up to 60 minutes):** Your developers typically prompt AI well before they commit, and the default is missing that work.

**Changing it recalculates AI-assisted metrics for past periods**, not just new activity, so previously-computed AI-assisted flags — and the AI-Assisted % values built on them — shift to match the new window.

**Affects:** [AI-Assisted Percentage](/insights-expo/expo-ai-adoption-impact-cost-ai-assisted-percentage), and the AI-Assisted status shown in PR drill-down tables.

---

### Ignore reviews from GitHub bot accounts

_In Settings UI: yes._ Settings → General, under **Scoring baseline**.

|  |  |
| --- | --- |
| **Default** | Off |
| **Type** | Checkbox (GitHub only) |

**What it does.** When on, a review left by a GitHub app or bot account is not treated as a human review, so it doesn't stop the clock on pickup time, review time, cycle time, or first response time. When off (the default), a bot's review counts like any other review.

Reviews from other git providers are counted either way. Turning the setting on recalculates those times for past PRs as well as new ones.

**Affects:** [Cycle Time](/insights-expo/expo-ai-adoption-flow-cycle-time) — its Pickup and Review phases and the total — and first response time.

---

### Developer Hourly Rate

Labeled **"Developer Hourly Rate ($)"** in the General tab, under **Scoring baseline**. _In Settings UI: yes._

|  |  |
| --- | --- |
| **Default** | $75 / hour |
| **Range** | Greater than $0 |
| **Type** | Currency |

**What it does.** The organization's hourly rate, used to calculate ROI on the AI Impact page for every developer who doesn't have their own hourly rate. To give a developer their own rate, set it in Settings → Developers — see [Per-developer hourly rate](#per-developer-hourly-rate). The AI Impact page turns "additional hours per developer per week" into a dollar figure with it:

```
ROI = (throughput speedup − 1) × working hours per week × sum of developer hourly rates / total AI spend
```

Each developer in the sum counts at their own hourly rate if they have one, and at this organization rate otherwise.

**How to set it.** Use your fully-loaded internal developer cost rate (salary × benefits × overhead, divided by working hours). Most orgs land between $50 and $200/hour. If you don't have a precise number, the default $75 is a reasonable industry midpoint for a senior IC.

**Affects:** [Productivity Uplift](/insights-expo/expo-ai-adoption-impact-cost-productivity-uplift) dollar figures and the AI Impact ROI cards.

---

### Working Hours Per Week

_In Settings UI: yes._ Settings → General, under **Scoring baseline**.

|  |  |
| --- | --- |
| **Default** | 40 hours |
| **Range** | 1 – 80 |
| **Type** | Number (hours) |

**What it does.** The standard work week for your organization. The AI Impact page uses it to turn throughput gains into hours and dollars saved per developer: the speedup over the baseline, minus 1, times this value gives the additional hours per developer per week. Set it to your org's real standard week (for example, 37.5 or 35) so the hours and dollar figures reflect it.

**Affects:** [Productivity Uplift](/insights-expo/expo-ai-adoption-impact-cost-productivity-uplift) — Additional Hours/Dev/Week, Additional $/Week — and the AI Impact ROI cards.

---

### Baseline Period

_In Settings UI: yes._ Settings → General, under **Scoring baseline**.

|  |  |
| --- | --- |
| **Default** | Set automatically, where it can be detected, to a month about 90 days before your organization first connected a repository; otherwise empty until you pick a month |
| **Type** | Month and year |

**What it does.** Sets the reference month used as the pre-AI baseline on the AI Impact page. Time saved and speedup are measured against this month: the all-developer median cycle time from this month serves as the pre-AI benchmark.

**When to change it.** Pick a different month if:

* Your AI rollout started later than the detected month, and you want to compare against a month just before the rollout.
* The detected month was unusual (a holiday month, a reorg, a release freeze) and would skew the comparison.
* You launch a new AI tool and want uplift measured from a specific pre-launch month.

**Affects:** [Productivity Uplift](/insights-expo/expo-ai-adoption-impact-cost-productivity-uplift), the AI Impact time-saved and ROI cards.

---

### Cycle Time Max Days

_In Settings UI: yes._ Settings → General, under **Scoring baseline**.

|  |  |
| --- | --- |
| **Default** | 30 days |
| **Range** | 1 – 365 |
| **Type** | Whole number (days) |

**What it does.** Caps the PR cycle-time lookback window. PRs whose work-start-to-merge duration exceeds this value are left out of cycle-time analytics, comparison, and team readiness views.

**When to change it.** Raise it if your team routinely works on PRs that stay open longer than 30 days and you want them counted. Lower it if long-running outliers still dominate your averages.

**Affects:** [Cycle Time](/insights-expo/expo-ai-adoption-flow-cycle-time) and its phase breakdown, the cycle-time comparisons on the AI Impact page, and team readiness views.

---

### Default Department

_In Settings UI: yes._

|  |  |
| --- | --- |
| **Default** | None (admin picks) |
| **Type** | Dropdown |

**What it does.** Pre-selects which teams are visible to a user the first time they hit the dashboard. Once a user has saved a different selection it sticks per-user in browser storage.

**When to set it.** Use this to anchor the dashboard to the largest, most-watched department in your org. Engineering leadership usually picks "Engineering" or the equivalent rolled-up group.

**Affects:** Initial view on /teams, /developers, /executive, and every page with a team filter.

---

### GitKraken Insights for Developers

_In Settings UI: yes._ Settings → General, under **Access to Insights**.

|  |  |
| --- | --- |
| **Default** | Off |
| **Type** | Checkbox ("Turn on for everyone in the organization") |

**What it does.** Gives everyone in your organization their own GitKraken Insights for Developers page — their adoption, autonomy, and outcomes, benchmarked against anonymized organization averages. Turning it on lets each person see their own data in the organization; it does not give anyone access to another person's data.

**Who can change it.** Only an organization owner or admin. Other roles — including roles that can change the rest of the General settings — see the checkbox disabled, with a note saying so.

**Affects:** Access only. It doesn't change any score or metric.

---

### AI cost rates

_In Settings UI: yes._ Settings → General, under **AI cost rates**. Shown only to users who can change settings.

|  |  |
| --- | --- |
| **Default** | 1× for every provider (no adjustment) |
| **Range** | Greater than 0, up to 10, with up to 4 decimal places |
| **Type** | Multiplier per provider, with an effective-from date |

**What it does.** Adjusts estimated AI costs to match what you pay each provider — for example, after a negotiated discount. Each provider's estimated costs are multiplied by its rate: use 0.8 for a 20% discount, or 1 for no adjustment. The section lists **Claude Code** (includes Cowork), **Codex**, **Cursor**, **Copilot**, and **OpenCode**. Each row shows the current multiplier, a **Default** or **Custom** badge, and — for a custom rate — the date it has applied since.

**How to change a rate.** Click **Edit** on the provider's row, then set:

* **Multiplier** — estimated costs are multiplied by this value.
* **Effective from** — the first day the multiplier applies. Dates are in UTC, and must be today or earlier. Saving again for the same date replaces that date's multiplier.

Click **Save rate**. The change saves immediately — no **Save changes** click needed. If you pick a date in the past, the form warns you that the provider's cost figures from that date onward (or up to the next rate change) will be recalculated with the new multiplier.

**History and reset.** **History** lists every rate change for a provider, with when it was made, and lets you delete one. **Reset to default** sets the provider back to 1× from today.

**Affects:** Every AI spend figure built from estimated provider costs — including [Spend by Tier](/insights-expo/expo-ai-adoption-impact-cost-spend-by-tier), total AI spend, and the AI Impact ROI cards that divide by it.

---

## Developers settings

### Per-developer hourly rate

_In Settings UI: yes._ Settings → Developers — open a developer with **Edit**, or select several developers and use the **Hourly rate** bulk action.

|  |  |
| --- | --- |
| **Default** | Empty (uses the organization's Developer Hourly Rate) |
| **Range** | 0.01 – 100,000, up to two decimal places |
| **Type** | Currency, per developer |

**What it does.** Labeled **Hourly rate ($)** on the developer's edit form. Sets this developer's own rate for cost and ROI on the AI Impact page, in place of the organization's [Developer Hourly Rate](#developer-hourly-rate). Leave it empty to use the organization rate.

**When to set it.** When your developers' loaded costs differ enough — by location, level, or contractor status — that one org-wide rate would distort the ROI figure.

**Affects:** [Productivity Uplift](/insights-expo/expo-ai-adoption-impact-cost-productivity-uplift) dollar figures and the AI Impact ROI cards. When developers have their own rates, the hourly rate shown on the AI Impact page is the average across the developers counted.

---

## Categorization settings

### Work categories

_In Settings UI: yes._ Settings → Categorization. The tab appears only for users who can change settings.

|  |  |
| --- | --- |
| **Default** | Feature, Enhancement, Bug Fix, Refactor, Chore |
| **Range** | Up to 20 categories |
| **Type** | Ordered list of categories |

**What it does.** Defines the categories GitKraken Insights sorts every PR and direct commit into. Each category has a **name** (up to 50 characters), a **description** (up to 2,000 characters) that says what belongs in it, and three flags:

* **CapEx** — work in this category counts as CapEx in the [CapEx / OpEx Split](/insights-expo/expo-ai-adoption-impact-cost-capex-opex). Unflagged categories count as OpEx.
* **Chore** — work in this category is what [Exclude Chore from Output Score](#exclude-chore-from-output-score) leaves out.
* **Customer evidence** — work in this category is capitalized unless it shows evidence of being customer-reported, in which case it's expensed.

In the defaults, Feature, Enhancement, and Refactor are flagged CapEx; Chore is flagged Chore.

**Changing the list.** Rename, reorder, or reword categories, change their flags, or add new ones, then save. Deleting a category may ask you to choose another category to move its items to. Every save creates a new version; the **History** table lists each version, who saved it, and its categories, and you can open any version to see it.

**Affects:** [CapEx / OpEx Split](/insights-expo/expo-ai-adoption-impact-cost-capex-opex), [Output Score](/insights-expo/expo-ai-adoption-output-output-score) (through the Chore flag), and the category shown on PRs and commits throughout the dashboard.

---

## Related reading

* [Playbook — Set tier weights for your org's maturity](/insights-expo/expo-ai-adoption-playbook-tier-weights)
* [Playbook — Roll out AI tooling with the Adoption Score](/insights-expo/expo-ai-adoption-playbook-ai-rollout)
