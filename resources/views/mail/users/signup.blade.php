@component('mail::message')
Hi.<br  />
<br  />
You are assigned as {{ $user->first_name }} on Salary EX admin.<br  />
<br  />
Please access to [ {{ $url }} ]( {{ $url }} ) with the following password.<br  />
<br  />
<br  />
Thank you,<br  />
<br  />
Salary EX.<br  />
@endcomponent