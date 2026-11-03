<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
 public function login(LoginRequest $r){$u=User::with('employee')->where('email',$r->email)->first();if(!$u||!Hash::check($r->password,$u->password))throw ValidationException::withMessages(['email'=>'Credenciales incorrectas.']);if(!$u->is_active)throw ValidationException::withMessages(['email'=>'Esta cuenta se encuentra desactivada.']);if($u->employee&&!$u->employee->active)throw ValidationException::withMessages(['email'=>'El acceso del colaborador se encuentra inactivo.']);$u->tokens()->where('name',$r->device_name)->delete();$u->forceFill(['last_login_at'=>now()])->save();return response()->json(['token'=>$u->createToken($r->device_name)->plainTextToken,'user'=>$u->load(['employee.workLocation','employee.workSchedule.days','employee.supervisor'])]);}
 public function me(Request $r){return $r->user()->load(['employee.workLocation','employee.workSchedule.days','employee.supervisor']);}
 public function logout(Request $r){$r->user()->currentAccessToken()->delete();return response()->noContent();}
}
