Eternity Insights
Dear Admin,

You have a new {{ \App\Mail\WebsiteEnquiry::label($lead['type']) }}.

Report Name: {{ $report->title ?? 'General website enquiry' }}
Contact Person: {{ $lead['full_name'] }}
Email ID: {{ $lead['email_id'] }}
Country: {{ $lead['country'] ?? 'Not provided' }}
Phone No: {{ $lead['contact_no'] }}
Company Name: {{ $lead['company'] ?? 'Not provided' }}
Job Role: {{ $lead['role'] ?? 'Not provided' }}
Client IP: {{ $clientIp ?? 'Not provided' }}
Received: {{ $lead['created_at'] }}

Message:
{{ $lead['message'] ?? 'Not provided' }}

View enquiry: {{ $enquiryId ? route('admin.edit',['enquiries',$enquiryId]) : route('admin.index','enquiries') }}

Reply to this email to contact {{ $lead['full_name'] }} directly.
