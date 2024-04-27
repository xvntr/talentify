@if (global_setting()->system_update == 1 &&  in_array('admin', user_roles()))
    @php
        $updateVersionInfo = \Froiden\Envato\Functions\EnvatoUpdate::updateVersionInfo();
    @endphp
    @if (isset($updateVersionInfo['lastVersion']))
   
    @endif
@endif
