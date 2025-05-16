<?php


use App\Models\StoreUser;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('store_.{id}',function ($user,$id){
//    return $storeId === $user->id;
    $data = StoreUser::where('user',$user->id)->where('shop',$id)->exists();
    return (integer)$data;
});


//Broadcast::channel('store.{id}',function ($user, $id){
////    return $storeId === $user->id;
//    $data = StoreUser::where('user',$user->id)->where('shop',$id)->exists();
//    return (integer)$data;
//});





