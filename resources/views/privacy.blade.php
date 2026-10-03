<x-app-layout>
    <x-slot name="header">
        <h1 class="h3 mb-1">Privacy notice</h1>
        <p class="text-muted mb-0">How LostMate AI handles your personal information, in line with the Data Privacy Act of 2012 (Republic Act No. 10173).</p>
    </x-slot>

    <div class="card">
        <div class="card-body p-4" style="max-width: 52rem;">
            <h2 class="h5">What we collect</h2>
            <ul>
                <li><strong>Account:</strong> your name, email address, and password (stored encrypted &mdash; nobody can read it, not even admins).</li>
                <li><strong>Profile (optional):</strong> department, course or position, year level, contact number, bio, and profile photo.</li>
                <li><strong>Reports:</strong> descriptions, locations, dates, and photos of lost or found items, plus any hidden details you record as a finder.</li>
                <li><strong>Activity:</strong> your claims, in-app messages, and notifications.</li>
            </ul>

            <h2 class="h5 mt-4">Why we collect it</h2>
            <p>Only to run the lost &amp; found service: matching lost items with found ones, letting owners and finders talk, verifying claims, and keeping the service safe from misuse.</p>

            <h2 class="h5 mt-4">Who can see what</h2>
            <ul>
                <li><strong>Other users</strong> see your name, profile photo, school details, bio, and your reports. They <strong>never</strong> see your email address or contact number.</li>
                <li><strong>Hidden details</strong> of found items are seen only by the finder and admins &mdash; never by claimants or the public.</li>
                <li><strong>Admins</strong> (school staff) can see accounts and reports to moderate the service. Every admin action is recorded in an audit log.</li>
            </ul>

            <h2 class="h5 mt-4">AI matching</h2>
            <p>To suggest matches, item details (name, category, color, brand, location, date, and description) are sent to an AI service. Your name, email, contact number, and hidden details are <strong>never</strong> sent. AI results are only suggestions; ownership is always confirmed by people.</p>

            <h2 class="h5 mt-4">Services we use</h2>
            <p>Photos are stored with Cloudinary, emails are sent through Brevo, and AI matching uses Google Gemini. They process data only to provide these functions.</p>

            <h2 class="h5 mt-4">Your rights</h2>
            <ul>
                <li><strong>Access and correct:</strong> view and edit your information anytime on your Profile page.</li>
                <li><strong>Erase:</strong> delete your account and all your data from the Profile page (<em>Delete my account</em>).</li>
                <li><strong>Object or ask questions:</strong> email <a href="mailto:lostmate.ai.app@gmail.com">lostmate.ai.app@gmail.com</a>.</li>
            </ul>

            <h2 class="h5 mt-4">How long we keep it</h2>
            <p>Until you delete your account. Found items nobody claims are closed by the school office after the unclaimed period. Deleting your account permanently removes your reports, claims, messages, and photos.</p>
        </div>
    </div>
</x-app-layout>
