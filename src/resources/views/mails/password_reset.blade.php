<div style="width: 50%; margin: 0 auto;">
  <h2 style="height: 30px; padding:20px; text-align: center; color: #008CBA; width: 50%; margin: 15px auto;">Hello, <strong>{{$data['name']}}</strong></h2>
  <p style="text-align: center;">You are receiving this email because we received a password reset request for your account.</p>
  <a style="background-color: #008CBA; width: 100%; border: none; color: white; padding: 15px 32px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;" href="{{ $data['url'] }}" target="_blank" rel="noopener">Reset Password</a>
  <p>If you did not request a password reset, no further action is required.</p>
  <span style="color: #7a7a8c;">Regards, RVM</span>
  <hr>

  <p style="font-size: 10px;">If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: <p>
  <a style="font-size: 10px;" href="{{$data['url']}}">{{$data['url']}}</a>
</div>
