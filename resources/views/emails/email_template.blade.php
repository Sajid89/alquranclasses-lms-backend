<html>
    <head>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    </head>
    <body>
        <div style="margin: 30px 30px; padding: 30px 30px; box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.5); 
            font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 400; 
            line-height: 18px; letter-spacing: 0px; text-align: left;"
        >
            <header style="display: flex; justify-content: space-between; margin-bottom: 50px;">
                <img src="{{ public_path('images/logo.png') }}" alt="Company Logo" style="flex: 1;">
                <div style="flex: 1; text-align: right;">
                    <img src="{{ public_path('images/linkedin.png') }}" alt="Alquranclasses LinkedIn Icon" style="margin-right: 10px;">
                    <img src="{{ public_path('images/facebook.png') }}" alt="Alquranclasses Facebook Icon" style="margin-right: 10px;">
                    <img src="{{ public_path('images/instagram.png') }}" alt="Alquranclasses Instagram Icon">
                </div>
            </header>

            @if (isset($heading))
                <h2>{{ $heading }}</h2>
            @endif

            @if (isset($sub_heading))
                <h3>Dear {{ $sub_heading }},</h3>
            @endif

            @if (isset($top_paragraphs))
                @foreach ($top_paragraphs as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            @endif

            @if (isset($list))
                <ul style="list-style-type: none; padding: 0;">
                    @foreach ($list as $key => $value)
                        <li>{{ $value }}</li>
                    @endforeach
                </ul>
            @endif

            @if (isset($button))
                <a type="button" href="{{ $button['url'] }}" 
                    target="_blank"
                    style="background-color: #01563F; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; display: inline-block;"
                >
                    {{ $button['text'] }}
                </a>
            @endif

            @if (isset($bottom_paragraphs))
                @foreach ($bottom_paragraphs as $paragraph)
                    <p>{!! $paragraph !!}</p>
                @endforeach
            @endif

            <div style="padding-top: 30px;">
                <p>
                    Warm regards,
                    <br>
                    Jazakum Allah Khairan
                    <br>
                    Support Team
                    <br>
                    AlQuranClasses.com
                </p>

                <p>
                    Contact Information:
                    <br>
                    Email: 
                        @if (isset($contact_email)) 
                            <a href="mailto:{{ $contact_email }}"
                                style="color: #01563F; text-decoration: none; font-weight: 600;"
                            >
                                {{ $contact_email }}
                            </a> 
                        @endif
                    <br>
                    Website: <a href="https://alquranclasses.com/" style="color: #01563F; text-decoration: none; font-weight: 600;">alquranclasses.com</a>
                </p>

                <p>
                    Customer Support Hotlines:
                    <br>
                    USA: <a href="tel:+1 (866) 302-4897" style="color: #01563F; text-decoration: none; font-weight: 600;">+1 (866) 302-4897</a>
                    <br>
                    UK: <a href="tel:+44 (142) 980-4123" style="color: #01563F; text-decoration: none; font-weight: 600;">+44 (142) 980-4123</a>
                    <br>
                    Canada: <a href="tel:+1 (866) 288-9181" style="color: #01563F; text-decoration: none; font-weight: 600;">+1 (866) 288-9181</a>
                </p>
            </div>

            <hr style="margin-top: 30px; margin-bottom: 50px;" />

            <div>
                <h3><strong>Connect with Us</strong></h3>
                <p style="color: #979797;">
                    You’re receiving this email because you are somehow Concerned 
                    with it. If this wasn’t you, please disregard this email or 
                    Let us know at 
                    <a href="mailto:support@alquranclasses.com"
                        style="color: #01563F; text-decoration: none; font-weight: 600;"    
                    >
                        support@alquranclasses.com
                    </a>
                </p>
                <div style="display: flex; align-items: center; color: #979797;">
                    <img src="{{ public_path('images/location_on.png') }}" alt="Alquranclasses Location Icon" style="margin-right: 10px;">
                    <p>310 Brookside road, Richmond Hill, L4C0K8, Ontario, Canada</p>
                </div>
            </div>

            <footer
                style="background-color: #01563F; text-align: center; color: #fff; padding: 20px 0;"
            >
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved.</p>
            </footer>
        </div>
    </body>
</html>
