<?php
/**
 * @package: turatel
 * @author: Faruk CAN <frkcn@bil.omu.edu.tr>
 */

Route::get('turatel', function(){
    echo 'Turatel sms!';
});
Route::get('send/{numbers}/{message}', 'Frkcn\Turatel\TuratelController@send');