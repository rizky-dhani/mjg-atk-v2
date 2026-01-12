<!DOCTYPE html>
<html>
<head>
    <title>ATK Stock Usage Update</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #f8f9fa;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
        }
        @media only screen and (max-width: 480px) {
            .container {
                padding: 10px;
            }
            h2 {
                font-size: 1.4em;
            }
            th, td {
                padding: 8px;
                font-size: 13px;
            }
            .button {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ATK Stock Usage Update</h2>
        <p>Dear {{ $recipientName ?? 'User' }},</p>

        @php
            $statusText = match ($actionStatus) {
                'submitted' => 'Submitted',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'partially_approved' => 'Partially Approved',
                default => ucfirst($actionStatus),
            };
            
            $actorName = $actor ? $actor->name : 'System';
        @endphp

        @if($actionStatus === 'submitted')
            <p>The following ATK Stock Usage has been <strong>Submitted</strong> by <strong>{{ $stockUsage->requester->name }}</strong>.</p>
            @if($isApprover)
                <p>Please review this usage request to <strong>Approve</strong> or <strong>Reject</strong>.</p>
            @endif
        @elseif($actionStatus === 'partially_approved')
            <p>The following ATK Stock Usage has been <strong>{{ $statusText }}</strong> by <strong>{{ $actorName }}</strong> and is now awaiting your action.</p>
            @if($isApprover)
                <p>Please review this usage request to <strong>Approve</strong> or <strong>Reject</strong>.</p>
            @endif
        @else
            <p>The following ATK Stock Usage has been <strong>{{ $statusText }}</strong> by <strong>{{ $actorName }}</strong>.</p>
        @endif
        
        @if($actionStatus === 'rejected' && $notes)
            <p><strong>Reason for Rejection:</strong> {{ $notes }}</p>
        @endif

        <div style="background-color: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <ul style="list-style: none; padding: 0;">
                <li><strong>Request Number:</strong> {{ $stockUsage->request_number }}</li>
                <li><strong>Requester:</strong> {{ $stockUsage->requester->name }} ({{ $stockUsage->division->name }})</li>
            </ul>
        </div>

        @if($viewUrl && $isApprover)
            <a href="{{ $viewUrl }}" class="button">View Usage Details</a>
        @endif

        <h3>Usage Items:</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Item</th>
                    <th style="text-align: center;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockUsage->atkStockUsageItems as $item)
                    <tr>
                        <td>{{ $item->category->name }}</td>
                        <td>{{ $item->item->name }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top: 30px; font-size: 0.9em; color: #666;">
            Thank you,<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>