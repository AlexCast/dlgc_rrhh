---
name: testing-for-broken-access-control
description: >-
  Systematically tests web applications and APIs for broken access control
  (OWASP A01:2021), including privilege escalation, missing function-level
  checks, insecure direct object references, and multi-tenant data leakage.
  Use during authorized penetration tests or RBAC/multi-tenant authorization
  audits.
domain: cybersecurity
subdomain: web-application-security
tags: [penetration-testing, access-control, authorization, owasp, privilege-escalation, web-security]
version: '1.0'
author: mahipal
license: Apache-2.0
source: https://github.com/mukul975/Anthropic-Cybersecurity-Skills
nist_csf: [PR.PS-01, ID.RA-01, PR.DS-10, DE.CM-01]
mitre_attack: [T1190, T1059.007, T1505.003, T1083, T1068]
---

# Testing for Broken Access Control

## When to Use

- Authorized penetration tests targeting OWASP A01:2021 - Broken Access Control
- Evaluating role-based access control (RBAC) across all endpoints
- Testing multi-tenant applications for cross-tenant data access
- Assessing API endpoints for missing/inconsistent authorization checks

## Prerequisites

- Written penetration testing agreement
- Multiple test accounts at each role level (admin, manager, user, guest)
- Application role matrix documenting expected access per role
- curl/httpie for manual endpoint testing, ffuf for endpoint discovery

## Workflow

### Step 1: Map Endpoints and Build an Access Control Matrix

Document every endpoint against every role (Allow/Deny/Own), including discovering hidden endpoints via `ffuf`.

### Step 2: Vertical Privilege Escalation

Attempt admin-only endpoints with lower-privilege tokens (including HTTP method overrides, e.g. `X-HTTP-Method-Override: DELETE`, and alternate verbs GET/POST/PUT/PATCH/DELETE/OPTIONS).

### Step 3: Horizontal Privilege Escalation

With User A's token, attempt to read/write User B's resources (`/api/users/102/profile`, etc.) — check both response status and response body length, since some apps return 200 with an empty/generic body.

### Step 4: Function-Level Access Control

- Test unauthenticated access to protected endpoints
- Test invalid/expired tokens
- Test mass-assignment role escalation: `{"role":"admin","is_admin":true}` in a profile update
- Test elevated-role registration payloads

### Step 5: Multi-Tenant Isolation

- Direct cross-tenant resource access
- Tenant switching via headers (`X-Tenant-ID`)
- Tenant ID smuggled in request body
- Enumerate tenant IDs with `ffuf`

## Key Concepts

| Concept | Description |
|---------|-------------|
| Vertical Privilege Escalation | Lower-privilege user reaching higher-privilege functionality |
| Horizontal Privilege Escalation | User accessing another user's resources at the same level |
| IDOR | Accessing objects by manipulating identifiers without authorization checks |
| Missing Function-Level Check | Endpoint exists but doesn't verify caller permission |

## Remediation (defensive takeaway)

1. Enforce server-side authorization on every endpoint — never rely on hiding UI elements
2. Centralize authorization in middleware rather than duplicating checks per-route
3. Verify object ownership before returning/modifying any resource (object-level authorization)
4. Never trust tenant/role context from client-supplied headers or body fields — derive from the authenticated session
5. Use allowlists for mass-assignment (explicitly whitelist updatable fields)
6. Audit-log all access control decisions
