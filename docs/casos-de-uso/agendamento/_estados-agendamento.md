# Modelo de Estados do Agendamento

Documento de referência compartilhado pelos casos de uso do módulo de
Agendamento. Define os estados possíveis de um agendamento e as
transições entre eles. Os UCs do módulo referenciam este documento em vez
de repetir a descrição.

## Estados

| Estado | Significado | Conta como ocupação do ambiente? |
|---|---|---|
| **Pendente** | Solicitação feita, aguardando decisão de quem tem `agendamentos.aprovar.qualquer`. | Não — múltiplos pendentes podem coexistir no mesmo horário/ambiente. |
| **Aprovado** | Solicitação aprovada; o ambiente está reservado para aquele horário. | **Sim** — bloqueia novas solicitações/aprovações em conflito. |
| **Rejeitado** | Solicitação recusada, com motivo registrado. | Não. |
| **Cancelado** | Agendamento cancelado (pelo solicitante enquanto Pendente, ou pelo admin em qualquer status), com motivo registrado. | Não. |

## Transições

```
[nova solicitação — UC-AGE-05]
        │
        ▼
    Pendente ──────────────► Rejeitado        (admin rejeita — UC-AGE-06)
        │  │
        │  └───────────────► Cancelado        (solicitante cancela enquanto Pendente — UC-AGE-08)
        │
        ▼ (admin aprova — UC-AGE-06)
    Aprovado ──────────────► Cancelado        (admin cancela — UC-AGE-08)

[agendamento fixo/recorrente — UC-AGE-07]
        │
        ▼ (nasce já aprovado)
    Aprovado ──────────────► Cancelado        (admin cancela ocorrência ou série — UC-AGE-08)
```

## Observações

- **Conflito de horário:** apenas agendamentos **Aprovados** contam como
  ocupação do ambiente. Múltiplas solicitações **Pendentes** para o mesmo
  horário/ambiente são permitidas; na aprovação, o sistema revalida que
  não há Aprovado em conflito (UC-AGE-05, UC-AGE-06).
- **Agendamentos fixos/recorrentes** (UC-AGE-07) nascem diretamente como
  **Aprovado**, sem passar por Pendente.
- **Cancelamento pelo solicitante** só é permitido enquanto Pendente; o
  Administrador pode cancelar em qualquer status (exceto já Cancelado) —
  ver UC-AGE-08.
- Os status de agendamento são independentes do ciclo de vida do usuário
  (`_estados-usuario.md`) e do modelo de permissões.
