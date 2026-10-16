# SWC — Smart WAN Control

> **Una red WAN que sabe cuándo deja de funcionar bien: detecta el fallo, avisa y reacciona antes de que lo notes.**

Plataforma inteligente para la monitorización y gestión de una red WAN empresarial virtualizada.

## Equipo

| Miembro | Ciclo | Rol principal |
|---|---|---|
| Eduardo Silva | DAM1 | Programación (aplicación, API, base de datos) |
| Alex Luis | ASIX1 | Red e infraestructura (WAN virtual, servicios, seguridad) |

**Módulo:** Proyecto Optativo · DAM1 · ASIX1

## Objetivo del repositorio

Este repositorio reúne el código, la documentación y el diseño de **SWC**, un proyecto cuyo objetivo es construir una plataforma web para supervisar y gestionar una red WAN empresarial multisede, totalmente virtualizada (sin hardware físico).

En él se versionan:

- `/src`: código de la aplicación (frontend, API, backend) y configuración de la infraestructura.
- `/docs/memoria`: memoria técnica en LaTeX.
- `/docs/adr`: decisiones de arquitectura.
- `/docs/actas`: actas de planificación y cierre de cada sprint.
- `/docs/retrospectivas`: retrospectivas de cada sprint.
- `/design`: diagramas de red, prototipos y diseños.

## Problema y cliente objetivo

Las empresas con varias sedes sufren latencia, pérdida de paquetes y saturación de la red, y suelen reaccionar cuando el problema ya ha afectado al servicio.

**Cliente objetivo:** pymes con varias sedes y, en concreto, sus administradores de red y sistemas, que necesitan saber rápidamente qué falla y dónde.

## Solución

Plataforma web que centraliza el estado de las conexiones entre sedes y permite:

- Ver el estado general de la red y de cada enlace.
- Recibir alertas y consultar el historial.
- Gestionar situaciones de fallo (reglas de reacción, ruta alternativa).

## Alcance

**MVP**

- WAN virtualizada multisede (inicialmente 3 sedes).
- Monitorización.
- Sistema de alertas.
- Reglas de reacción.
- Simulación de fallos.

**Ampliaciones previstas**

- Gráficas avanzadas de latencia y disponibilidad.
- Gestión de usuarios (administrador y operador).
- IPv6.
- Alta de nuevas sedes.

## Stack tecnológico (previsto)

| Capa | Tecnologías |
|---|---|
| Red | GNS3 · FRRouting · OSPF · VLAN · VPN |
| Sistemas | Linux |
| Servicios | DNS · DHCP |
| Datos | MySQL |
| Aplicación | Frontend → API → Backend + Dashboard |
| Seguridad | Firewall · VLAN · autenticación · control de acceso |

## Metodología

Trabajo por sprints con GitHub Projects (tablero Kanban), Issues, Milestones y Pull Requests. Planificación prevista en 10 sprints, desde el análisis hasta la validación y presentación final.

### Roles del equipo (Sprint 1)

| Rol | Responsable |
|---|---|
| Coordinación / Scrum Master | _por asignar_ |
| Responsable técnico (aplicación) | Eduardo Silva |
| Responsable técnico (red) | Alex Luis |
| Responsable de documentación | _por asignar_ |

Los roles pueden rotar entre sprints.

### Definition of Done

Una tarea se considera terminada cuando:

1. Cumple los criterios de aceptación descritos en su Issue.
2. El código o la configuración está en una Pull Request revisada por otro miembro y mergeada en `main`.
3. Está probada y funciona (o se ha verificado en la red virtual).
4. La documentación afectada está actualizada (README, memoria LaTeX, ADR si procede).
5. El Issue está cerrado y en la columna **Hecho** del tablero.

## Estado actual

**Sprint 1 (07/10/2026 – 21/10/2026):** arranque del proyecto, backlog inicial y capítulo 1 de la memoria.
