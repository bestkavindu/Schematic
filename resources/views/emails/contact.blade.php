<x-mail::message>
# New contact message

**Topic:** {{ $topic }}
**From:** {{ $senderName }} ({{ $senderEmail }})
@if ($company)
**Company:** {{ $company }}
@endif
@if ($teamSize)
**Team size:** {{ $teamSize }}
@endif

---

{{ $body }}

---

Reply directly to this email to respond to {{ $senderName }}.
</x-mail::message>
