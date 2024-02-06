@extends('emails.layout')

@section('content')
    @php
        date_default_timezone_set('Europe/Istanbul');
        $date = date('Y-m-d H:i:s');
 @endphp
<div style="padding: 10px 0; text-align: center">
    <h2 style="text-align: center;">Your Access Verify Code:</h2>
    <span style="display: inline-block; margin-top: 10px;text-align: center; border: solid 1px #20c997; background: #20c997; padding: 5px 20px; border-radius: 4px; color: white; font-weight: 600; letter-spacing: 6px; font-size: 30px;">{{ $code }}</span>
    <p style="display: inline-block; margin-top: 30px; padding: 5px 20px; font-size: 14px;">
        This security code is valid for 10 minutes. <br>Generated at {{ $date }} (UTC+3) <br> Please enter it in the corresponding input field.
    </p>
</div>
@endsection
