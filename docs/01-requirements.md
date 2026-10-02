# Requirements baseline

Status: planned unless the implementation column says source prepared. Runtime verification is pending.

## Problem and stakeholders

Householders lack a structured place to discover providers, arrange services, and track work. Providers need a way to present expertise and handle requests. Customers, providers, administrators, students, assessors, and the university are stakeholders.

## Objectives and scope

Deliver the end-to-end discovery → booking → acceptance → messaging → completion → review workflow, with administrative oversight. Use Laravel conventions, reusable Blade UI, MySQL constraints, validation, and authorization. Exclude payments, GPS, real-time chat, native apps, automatic matching, and formal provider verification.

## Assumptions and constraints

One role per account. Customers choose one provider and one category per booking. Providers must offer the selected category. Preferred times use Asia/Colombo. Availability is initially descriptive, not an automatic appointment scheduler. No guarantee of qualifications. Academic timeline, team size, hosting, and exact cancellation policy remain to be confirmed.

## Initial traceability and MoSCoW

| Requirement | Description | Priority | Implementation | Tests |
|---|---|---|---|---|
| FR-01 | Customer/provider registration | Must | Source prepared | AUTH-01–05 |
| FR-02 | Login/logout | Must | Source prepared | AUTH-06–07 |
| FR-03 | Own profile management | Must | Planned | Pending |
| FR-04 | Provider services, area, experience, availability | Must | Planned | Pending |
| FR-05–07 | Browse/filter providers and read reviews | Must | Planned | Pending |
| FR-08–14 | Create, track, transition, cancel bookings | Must | Planned | Pending |
| FR-15 | Participant messaging | Must | Planned | Pending |
| FR-16 | One review per completed owned booking | Must | Planned | Pending |
| FR-17–18 | Complaints and resolution | Must | Planned | Pending |
| FR-19–22 | Admin categories/users/bookings/review moderation | Must | Planned | Pending |
| FR-23 | Role-specific dashboards | Must | Access shells prepared; metrics planned | AUTH-08 |
| FR-24 | Full public homepage | Must | Welcome shell only | Pending |
| FR-25 | Validation feedback and empty states | Must | Authentication errors prepared | AUTH-02–06 |
| FR-26 | Booking filtering and pagination | Should | Planned | Pending |
| FR-27 | Clearly labeled demo testimonials | Could | Planned | Pending |

## Non-functional requirements

NFR-01: authenticated actions enforce role and ownership. NFR-02: server validation and CSRF protection. NFR-03: escape user output. NFR-04: restrict private addresses/messages. NFR-05: enforce valid transitions and unique reviews. NFR-06: clear forms/errors. NFR-07: responsive at 375/768/1440px. NFR-08: keyboard navigation and readable contrast. NFR-09: target response under two seconds in a documented demo environment. NFR-10: reusable UI and conventional Laravel code. NFR-11: atomic multi-record operations. NFR-12: production debug disabled. NFR-13: major feature tests. NFR-14: reproducible setup. NFR-15: consistent design. NFR-16: backup/restore instructions before deployment.

## Process and backlog

Use iterative and incremental development: design → implement → test → document → review. Next increments: provider profiles and categories; public discovery; booking lifecycle; messaging; reviews; administration; deployment. Tests and security accompany each increment.

Stories: As a customer I can register without choosing admin privileges; as a provider I can access only my workspace; as a customer I can book an offered service; as a provider I can accept only assigned requests; as a customer I can review only my completed booking. Each later story must receive acceptance criteria and linked tests before implementation.

Risks: excessive scope, ID manipulation, invalid status transitions, duplicate reviews, concurrent booking updates, leaked addresses, and documentation drift. Resolve with prioritization, policies, constraints, transactional updates where needed, privacy checks, and traceability.
