---
title: Connect Agent OTel Data
description: Send Claude Code, OpenAI Codex, GitHub Copilot, and OpenCode telemetry to GitKraken Insights over OpenTelemetry — enable Agent OTel Data, roll out each agent's config, and see exactly what the telemetry contains.
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
integrations: [Claude Code, Codex, GitHub Copilot, OpenCode]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: blue
    card_description: Claude Code, OpenAI Codex, GitHub Copilot, and OpenCode telemetry
    card_icon: broadcast
    nav_category: connect-your-data
    nav_label: Agent OTel Data
    nav_order: 55
    page_type: content
---
<kbd>Last updated: October 2026</kbd>

Claude Code, OpenAI Codex, and GitHub Copilot report usage to GitKraken Insights through OpenTelemetry (OTel), and so does [OpenCode](#opencode) where it's enabled on request. They all share one connection — **Agent OTel Data** — with one collector endpoint and one organization auth token. GitKraken generates a ready-to-paste config for each agent; you roll it out once, and usage flows in as developers work.

> **Before you start:** make sure you have the right access. See the [prerequisites table](/insights-expo/expo-ai-adoption-connect-your-data#before-you-start-gather-access) on the Connect Your Data overview.

---

## Enable Agent OTel Data

1. In gitkraken.dev, open **Insights → Settings → Data Connections**.
2. Under **Add data source**, in the **AI Coding Agents** group, click **+** next to **Agent OTel Data**.
3. Click **Enable**. GitKraken generates your collector endpoint and token and opens the **Agent OTel Data setup** view.

To come back to the setup view later, click **View setup** on the Agent OTel Data connection.

The setup view has three parts:

- **Your org auth token** — the **Collector endpoint** telemetry is sent to, and the **Authentication token** for your organization. One token is shared by every agent, so if you've already set up one agent, the others reuse the same credential.
- **Choose your agent** — pick **Claude Code**, **OpenAI Codex**, or **GitHub Copilot** (plus **OpenCode**, if it's enabled for your organization) to see its config. Each card also shows that agent's sync status.
- **The agent's config** — a ready-to-paste snippet with your endpoint and token already filled in. The token is masked on screen; **Show token** reveals it, and **Copy** copies the complete snippet. Claude Code and GitHub Copilot offer more than one setup method — pick the method above the snippet.

<!-- FLAG FOR HUMAN REVIEW: claude-code-codex-setup.png predates the Agent OTel Data setup view (it shows the older "Claude Code & Codex" page with one config field per tool, not the Choose your agent cards). It was also captured against the dev collector, so its Collector endpoint reads otel-devex-dev.gitkraken.com. Retake against production. -->
<figure>
  <img src="/wp-content/uploads/claude-code-codex-setup.png" class="help-center-img img-bordered" alt="The Claude Code and Codex setup page in GitKraken Insights, showing the shared org auth token with its Collector endpoint and masked Authentication token, followed by a masked Claude Code config field and a masked OpenAI Codex config field, each with reveal and copy buttons" />
  <figcaption>The setup page — one org auth token, plus a ready-to-copy config for each agent.</figcaption>
</figure>

Always copy the real snippet from the setup view rather than retyping the examples on this page.

> **Keep the auth token private.** It authenticates telemetry for your whole organization — treat it like any other secret credential.

> **Data appears on the next fresh session.** There's no backfill — usage starts flowing the next time each developer runs the agent after the config is applied, and can take up to 24 hours to reach your dashboards. (Claude Code OTel instrumentation began March 5, 2026; activity before that date isn't available.) See [When your AI tool data appears](/insights-expo/expo-ai-adoption-connect-your-data#when-your-ai-tool-data-appears).

---

## Claude Code

Set up Claude Code telemetry one of two ways: a **Team config** that an organization Owner applies once, or a **Claude apps gateway** that your developers already sign in through. Pick the method in the setup view.

### Team config — organization-managed settings

The recommended rollout is server-managed settings in claude.ai: an Owner pastes the snippet once and every developer's Claude Code picks it up automatically. This requires a **Claude for Teams or Enterprise** plan.

1. In the setup view, choose **Claude Code → Team config** and copy the **Claude Code config** snippet.
2. As an organization Owner, go to **Admin Settings → Claude Code → Managed settings** ([claude.ai/admin-settings/claude-code](https://claude.ai/admin-settings/claude-code)).
3. Paste the snippet into the managed settings JSON. If managed settings already exist, **merge the `"env"` keys** into the existing `"env"` block rather than replacing the configuration — everything here applies org-wide.
4. Save.

The snippet looks like this, with your own token filled in:

```json
{
  "env": {
    "CLAUDE_CODE_ENABLE_TELEMETRY": "1",
    "OTEL_EXPORTER_OTLP_LOGS_ENDPOINT": "https://otel-devex.gitkraken.com/v1/logs",
    "OTEL_EXPORTER_OTLP_LOGS_HEADERS": "Authorization=Basic <YOUR_ORG_AUTH_TOKEN>",
    "OTEL_EXPORTER_OTLP_LOGS_PROTOCOL": "http/protobuf",
    "OTEL_LOGS_EXPORTER": "otlp"
  }
}
```

> **This requires the Claude Code organization Owner.** Admins do not have permission to change org-managed settings. If the Managed settings link redirects you elsewhere, your account doesn't have the Owner role.

> **Clients need a restart.** Claude Code also polls hourly, but OpenTelemetry configuration specifically takes effect on the next full restart.

> **Heads-up for your developers — warn them first.** The moment org telemetry settings change, Anthropic notifies *every developer* on their next Claude Code session that organization settings were modified, and the notice uses deliberately cautious wording (it warns that changed settings *could* run code). Custom environment variables require a one-time security approval, so accepting it is expected and required. This is Anthropic's standard behavior, not GitKraken's. Send your team a quick message ahead of time so no one is surprised. Suggested note:
>
> > *"Heads up — we're turning on Claude Code usage telemetry for the team so we can measure AI adoption. On your next Claude Code session you'll see a notice that org settings changed; that's expected — go ahead and accept it. We are not collecting your prompts."*

> **Privacy:** the snippet sends usage metadata — not prompt content, responses, or code. See [What GitKraken collects from agent telemetry](#what-gitkraken-collects-from-agent-telemetry).

> Managed settings take precedence over developers' own user and project settings, so individual configs can't accidentally turn the telemetry off.

> **If your organization already sends Claude Code metrics to another vendor,** contact GitKraken support for a combined example.

**Using Amazon Bedrock, Google Cloud Vertex AI, or Microsoft Foundry?** If your developers reach Claude Code through a self-hosted Claude apps gateway, use the [Claude apps gateway](#claude-apps-gateway) method instead.

### Claude apps gateway

If your developers use Claude Code through a self-hosted Claude apps gateway (Amazon Bedrock, Google Cloud Vertex AI, or Microsoft Foundry), configure telemetry once on the gateway instead of on each developer's machine. Developers don't configure anything.

1. In the setup view, choose **Claude Code → Claude apps gateway** and copy the **gateway.yaml telemetry block**.
2. Add the block to your gateway's `gateway.yaml`. If `gateway.yaml` already has a `telemetry.forward_to` list, add this as another entry in that list rather than replacing it.
3. Restart the gateway.
4. Each developer approves the gateway's telemetry settings once, in their next Claude Code session.

The block looks like this, with your own token filled in:

```yaml
telemetry:
  forward_to:
    - url: https://otel-devex.gitkraken.com
      headers:
        Authorization: Basic <YOUR_ORG_AUTH_TOKEN>
      metrics: false
      logs: true
```

Reference: [Anthropic — Claude apps gateway telemetry](https://code.claude.com/docs/en/claude-apps-gateway-config#telemetry).

---

## OpenAI Codex

The Codex CLI reads its settings from a `config.toml` file in a `.codex` directory in the user's home folder:

| Platform | Path |
| --- | --- |
| macOS | `~/.codex/config.toml` |
| Linux | `~/.codex/config.toml` |
| Windows | `%USERPROFILE%\.codex\config.toml` |

Create the directory and file if they don't exist. If the file already has other settings, **append** the `[otel]` block rather than overwriting the file. The snippet is identical on every platform — only the location differs:

```toml
[otel]
environment = "prod"

# Logs
exporter = { otlp-http = {
  endpoint = "https://otel-devex.gitkraken.com/v1/logs",
  protocol = "binary",
  headers = { Authorization = "Basic <YOUR_ORG_AUTH_TOKEN>" }
}}
```

**Option A — managed rollout via MDM (recommended for organizations).** Because the token is the same for the whole organization, a single managed configuration works for every developer on a given platform:

1. In the setup view, choose **OpenAI Codex** and copy the **OpenAI Codex config** snippet.
2. Package it as a managed file deployed to the per-user Codex path — `~/.codex/config.toml` on macOS and Linux, `%USERPROFILE%\.codex\config.toml` on Windows. Merge it into an existing `config.toml` if your developers already use one.
3. Push the profile to your fleet with your MDM tool — Jamf, Kandji, or Intune for macOS; Intune, Group Policy, or your endpoint manager for Windows.
4. Confirm the file landed on a sample machine.

**Option B — individual developer setup.** Any developer can connect their own machine in under a minute.

On macOS or Linux:

```bash
mkdir -p ~/.codex
nano ~/.codex/config.toml   # or: open -e ~/.codex/config.toml on macOS
```

On Windows, in PowerShell:

```powershell
New-Item -ItemType Directory -Force -Path "$env:USERPROFILE\.codex" | Out-Null
notepad "$env:USERPROFILE\.codex\config.toml"
```

Paste the snippet, then save. New Codex sessions start sending telemetry automatically — a session that was already running when the config was added won't report.

> **Windows — watch the file extension.** In Notepad's Save dialog, set **Save as type** to *All Files* so the file is saved as `config.toml` and not `config.toml.txt`.

---

## OpenCode

> **Note — available on request.** OpenCode telemetry isn't turned on for every organization. To enable it, contact your GitKraken account team. Once it's enabled, **OpenCode** appears under **Choose your agent** in the Agent OTel Data setup view.

OpenCode reports through a GitKraken plugin. **Setup is a per-developer action:** there is no team-wide rollout, so each developer installs the plugin and adds the configuration on their own machine, with their own email. When your account team enables OpenCode, they provide the plugin package and its install steps.

Requires OpenCode 1.18.0 or later.

1. Install the plugin package from your account team, following the install steps that come with it.
2. In the setup view, choose **OpenCode** and copy the **OpenCode config** snippet.
3. Add it to your global OpenCode config — `~/.config/opencode/opencode.json` on macOS and Linux, `%USERPROFILE%\.config\opencode\opencode.json` on Windows. Create the file if it doesn't exist; if it does, merge the `plugin` entry into it rather than replacing the file.
4. Replace `YOUR_EMAIL` with your own email address, so your usage is attributed to you.
5. Start a new OpenCode session.

The snippet looks like this, with your own token filled in. If the plugin's install steps tell you to reference it by its installed location instead of by name, use that path as the first entry in the `plugin` list:

```json
{
  "$schema": "https://opencode.ai/config.json",
  "plugin": [
    [
      "@gitkraken/opencode-otel-plugin",
      {
        "debug": true,
        "endpoint": "https://otel-devex.gitkraken.com",
        "headers": {
          "Authorization": "Basic <YOUR_ORG_AUTH_TOKEN>"
        },
        "userEmail": "YOUR_EMAIL"
      }
    ]
  ]
}
```

---

## GitHub Copilot (OpenTelemetry)

GitHub Copilot can send its own telemetry from each developer's Copilot CLI and VS Code. Set it up one of two ways: deploy **managed settings** across the organization (GitHub Enterprise), or have each developer configure the **CLI** and **VS Code**. Either way, every device must identify its developer — see [Set a developer identity on every device](#set-a-developer-identity-on-every-device).

In the setup view, choose **GitHub Copilot**, then pick a method: **Managed settings (Enterprise)**, **CLI env vars**, or **VS Code settings**.

### Managed settings (Enterprise)

Deploy the **Managed settings JSON** through GitHub Enterprise managed settings to configure Copilot across the organization. Managed settings cover both VS Code and the Copilot CLI, and take precedence over developers' own environment and user settings.

```json
{
  "telemetry": {
    "captureContent": false,
    "enabled": true,
    "endpoint": "https://otel-devex.gitkraken.com",
    "headers": {
      "Authorization": "Basic <YOUR_ORG_AUTH_TOKEN>"
    },
    "lockCaptureContent": true,
    "protocol": "http/protobuf"
  }
}
```

The managed settings deliberately carry no developer identity. Each device still needs its own — see [Set a developer identity on every device](#set-a-developer-identity-on-every-device).

### CLI env vars

Add the **CLI environment block** to each developer's shell environment before starting Copilot CLI. Replace `DEVELOPER_EMAIL` with that developer's email address.

```bash
export COPILOT_OTEL_ENABLED=true
export OTEL_EXPORTER_OTLP_ENDPOINT="https://otel-devex.gitkraken.com"
export OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf
export OTEL_EXPORTER_OTLP_HEADERS="Authorization=Basic <YOUR_ORG_AUTH_TOKEN>"
export OTEL_RESOURCE_ATTRIBUTES="user.email=DEVELOPER_EMAIL"
export OTEL_INSTRUMENTATION_GENAI_CAPTURE_MESSAGE_CONTENT=false
```

### VS Code settings

Add the **VS Code config** setting to VS Code's settings, set the same environment variables as the [CLI env vars](#cli-env-vars) block, then restart VS Code.

```json
{
  "github.copilot.chat.otel.enabled": true
}
```

> **The setting alone sends nothing.** It only turns telemetry on — the endpoint, auth token, and developer identity come from the environment variables, so VS Code must start in an environment where they're set.

### Set a developer identity on every device

> **Every device must set its own developer identity**, either through manual config or your MDM. Set `OTEL_RESOURCE_ATTRIBUTES` to include `user.email=<developer email>` (replace `DEVELOPER_EMAIL` in the snippet). Do not put one shared email in managed settings, or every event will be attributed to the same person.

### Copilot telemetry and the GitHub Copilot connection

GitHub Copilot has two separate connections, and you can connect either or both:

| | **GitHub Copilot** connection (API) | **Agent OTel Data** — GitHub Copilot |
| --- | --- | --- |
| **Set up with** | A personal access token (classic, or fine-grained for an organization), in the **GitHub Copilot** tile | The Copilot config from the Agent OTel Data setup view, on each device or through managed settings |
| **What it reports** | GitHub's daily Copilot usage metrics for an organization or enterprise | Telemetry from each developer's Copilot CLI and VS Code, grouped into sessions |
| **History** | Up to the last 90 days | None — starts with each developer's next session |
| **AI Sessions** | Not grouped into sessions, so its activity appears on the other AI Adoption pages | Included |

Both report as **GitHub Copilot** in Insights. To set up the API connection, see [GitHub Copilot](/insights-expo/expo-ai-adoption-connect-ai-tools#github-copilot) on the AI Coding Tools page.

---

## What GitKraken collects from agent telemetry

Claude Code and OpenAI Codex send limited event metadata through their built-in OpenTelemetry exporters. GitKraken collects only the fields the source CLI emits, and only while telemetry is enabled — if telemetry is turned off in the CLI, nothing is sent.

### What's collected

Depending on what the CLI provides, this may include:

- Event type and timestamp
- Provider and model identifier
- Tool name, when an agent invokes a tool
- Token and cost metrics, when available
- Session or conversation identifiers, used to group related activity
- User email and, where the CLI provides them, team or organization identifiers
- Limited operational metadata, such as duration, success or failure status, and cache-related metrics

### What isn't collected

- Prompt text
- Model responses
- Tool output contents
- Source code
- File contents
- File paths
- Conversation transcripts or chat history
- Environment variables, secrets, or credentials
- Network request or response bodies exchanged with the model provider

### How identity is handled

- **User email** is stored when the CLI provides it, so you can attribute usage within your organization.
- **Team or organization identifiers** are stored only when the CLI or its telemetry configuration provides them.
- **Session and conversation identifiers** group related activity. They aren't intended to contain prompt or response content.

### How the data is used

The telemetry powers your reporting in GitKraken Insights and product operations:

- Adoption metrics such as active users, prompts, and sessions
- Token and cost reporting, when those values are available
- Tool usage metrics, such as invocation volume and success rates
- Team- and organization-level usage rollups

> **Note — GitHub Copilot:** the Copilot snippets GitKraken generates turn content capture off — `captureContent` is `false` in the managed settings (and locked with `lockCaptureContent`), and the CLI block sets `OTEL_INSTRUMENTATION_GENAI_CAPTURE_MESSAGE_CONTENT=false`.

References: [Anthropic — Claude Code observability](https://code.claude.com/docs/en/agent-sdk/observability) and [OpenAI — Running Codex safely](https://openai.com/index/running-codex-safely/).

---

## Troubleshooting

| Symptom | What's happening | Fix |
| --- | --- | --- |
| **Can't change Claude Code org settings** | You're an admin, not the org Owner | Only the Anthropic org **Owner** can apply the telemetry snippet |
| **Claude Code settings applied but no telemetry** | Clients pick up OTel config only on a full restart | Have developers fully restart Claude Code |
| **No Claude Code telemetry through the gateway** | The gateway hasn't been restarted, or developers haven't approved its telemetry settings yet | Restart the gateway after editing `gateway.yaml`, and have developers start a new Claude Code session and approve the settings |
| **No Codex telemetry** | The snippet is in the wrong path, Notepad saved it as `config.toml.txt`, or the developer is running a pre-release build of the Codex desktop app | Verify the per-OS `config.toml` path and the file extension, and have developers run a stable Codex build — pre-release and alpha builds can drop OTel events |
| **All Copilot telemetry is attributed to one person** | One shared email was set for every device | Set `OTEL_RESOURCE_ATTRIBUTES` to `user.email=<developer email>` per device, not in managed settings |
| **No Copilot telemetry from VS Code** | Only the settings toggle is set, or VS Code wasn't restarted | Set the environment variables where VS Code starts, then restart VS Code |

For general connection troubleshooting, see [Troubleshooting setup](/insights-expo/expo-ai-adoption-connect-your-data#troubleshooting-setup) on the Connect Your Data overview.

---

## After connecting

Once your connection is active, continue with the remaining setup steps on the [Connect Your Data](/insights-expo/expo-ai-adoption-connect-your-data) overview:

- [Set your benchmarks](/insights-expo/expo-ai-adoption-connect-your-data#set-your-benchmarks)
- [Map developer identities](/insights-expo/expo-ai-adoption-connect-your-data#map-developer-identities)
- [Invite your team](/insights-expo/expo-ai-adoption-connect-your-data#invite-your-team)
