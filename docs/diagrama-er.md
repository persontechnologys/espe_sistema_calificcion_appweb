# Diagrama Entidad-Relación

Generado a partir de las migraciones en `database/migrations`. GitHub renderiza el
bloque Mermaid directamente.

```mermaid
erDiagram
    users ||--o{ course_offerings : "dicta (teacher_id)"
    users ||--o{ audit_logs : "realiza"
    academic_periods ||--o{ course_offerings : "agrupa"
    subjects ||--o{ course_offerings : "se oferta en"
    course_offerings ||--o{ enrollments : "tiene"
    students ||--o{ enrollments : "se matricula"
    enrollments ||--o| grades : "tiene 0..1"
    enrollments ||--o{ audit_logs : "historial"

    users {
        bigint id PK
        varchar name
        varchar email UK
        varchar password "hash bcrypt"
        varchar role "CHECK admin | teacher"
        boolean is_active
        varchar institutional_id UK "NULL solo si no es teacher (CHECK)"
    }
    students {
        bigint id PK
        varchar institutional_id UK "ej. L00423704"
        varchar first_names
        varchar last_names
        varchar email "opcional"
        boolean is_active
    }
    subjects {
        bigint id PK
        varchar code UK "ej. COMP-A0G11"
        varchar name
        boolean is_active
    }
    academic_periods {
        bigint id PK
        varchar code UK "ej. 202451"
        varchar description "PREGRADO S-II OCT 24 - MAR 25"
        date start_date
        date end_date "CHECK >= start_date"
        varchar status "CHECK planned | open | closed"
    }
    course_offerings {
        bigint id PK
        bigint academic_period_id FK "UK(academic_period_id, nrc)"
        bigint subject_id FK
        bigint teacher_id FK "users.id"
        varchar nrc "único dentro del período"
        varchar classroom "opcional"
        varchar campus "opcional"
    }
    enrollments {
        bigint id PK
        bigint student_id FK "UK(student_id, course_offering_id)"
        bigint course_offering_id FK
    }
    grades {
        bigint id PK
        bigint enrollment_id FK,UK
        decimal partial_1 "DECIMAL(5,2) NULL, CHECK 0-20"
        decimal partial_2 "DECIMAL(5,2) NULL, CHECK 0-20"
        decimal partial_3 "DECIMAL(5,2) NULL, CHECK 0-20"
        int version "bloqueo optimista"
    }
    audit_logs {
        bigint id PK
        bigint user_id FK
        bigint enrollment_id FK
        varchar operation "CHECK registro | correccion"
        json old_values
        json new_values
        text reason "obligatorio si correccion (CHECK)"
        timestamp created_at
    }
```

## Notas de diseño

- **Claves técnicas vs. códigos institucionales.** Todas las tablas usan `id` autoincremental
  como PK. El ID institucional, el código de asignatura, el código de período y el NRC son
  columnas de texto con su propia restricción `UNIQUE`.
- **Forma normal.** El esquema está en 3FN: cada atributo depende solo de la clave de su
  tabla. Total, promedio y estado no se almacenan porque dependen de los parciales
  (serían dependencias transitivas y podrían quedar desactualizados).
- **Borrado.** Todas las FK son `ON DELETE RESTRICT`; los registros en uso se inactivan.
- **Índices.** Además de PK/UNIQUE: `course_offerings(teacher_id)` para "mis ofertas",
  `course_offerings(subject_id)`, `enrollments(course_offering_id)` para el listado y el
  reporte de un NRC, `students(last_names, first_names)` para ordenar, y
  `audit_logs(enrollment_id)` / `audit_logs(created_at)` para el historial.
- Las tablas técnicas `sessions` y `cache` de Laravel se omiten del diagrama.
