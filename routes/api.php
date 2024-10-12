<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\adminController;
use App\Http\Controllers\Api\childrenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function () {

// });



Route::post('/login', [MemberController::class, 'login']);
Route::post('/logout', [MemberController::class, 'logout']);



//member API
// Route::get('/Allmember',[MemberController::class,'fetchAllMembers']);
// Route::post('/Addmember', [MemberController::class, 'Addmember']);
// Route::get('/member/{userid}',[MemberController::class,'GetMember']);
// Route::put('/member/{userid}/update',[MemberController::class,'updateMember']);
// Route::delete('/member/{userid}/delete',[MemberController::class,'deleteMember']);

// //children API
// Route::get('/Allchildren',[childrenController::class,'FetchAllChildren']);
// Route::post('/Addchildren',[childrenController::class,'AddChild']);
// Route::delete('/children/{parentid}/{id}/delete',[childrenController::class,'deleteChildren']);
// Route::put('/children/{id}/update',[childrenController::class,'updateChild']);
// Route::get('/children/{parentid}/viewallchildren',[childrenController::class,'viewchildren']);
// Route::get('/children/{parentid}/{id}/viewchild',[childrenController::class,'viewchild']);

// //admin API
// // Route::get('Alltitle',[adminController::class,'FetchAlltitle']);

// Route::get('/Alltitle',[adminController::class,'FetchAlltitle']);
// Route::post('/Addtitle',[adminController::class,'Addnewtitle']);
// Route::delete('/title/{id}/delete',[adminController::class,'deleteTitle']);
// Route::put('/title/{id}/update',[adminController::class,'updateTitle']);


// //National
// Route::get('/Allnational',[adminController::class,'FetchAllNatinal']);
// Route::post('/Addnational',[adminController::class,'AddNewNatinal']);
// Route::put('/updatenational/{code}/update',[adminController::class,'UpdateNational']);
// Route::delete('/deletenational/{code}/delete',[adminController::class,'deleteNational']);

// //State
// Route::get('/Allstate',[adminController::class,'FetchAllState']);
// Route::post('/Addstate',[adminController::class,'AddNewState']);
// Route::get('/State/{scode}',[adminController::class,'GetAState']);


//protected route
//Route::group(['middleware' => ['auth:sanctum']],function(){

Route::get('/member/{userid}', [MemberController::class, 'GetMember']);
Route::post('/member/{userid}/update', [MemberController::class, 'updateMember']);
Route::delete('/member/{userid}/delete', [MemberController::class, 'deleteMember']);
Route::get('/Allmember', [MemberController::class, 'FetchAllMembers']);
Route::get('/Fetchmemberbyparish/{pcode}', [MemberController::class, 'Fetchmemberbyparish']);
// });


Route::post('Addmember', [MemberController::class, 'Addmember']);
Route::post('ChangePassword/{id}', [MemberController::class, 'ChangePassword']);
Route::get('FetchAllCountries', [adminController::class, 'FetchAllCountry']);
Route::get('FetchAllState', [adminController::class, 'FetchAllStates']);
Route::post('AddNewState', [adminController::class, 'AddNewStates']);
Route::post('AddNewCountry', [adminController::class, 'AddNewCountry']);
Route::post('AddMultipleCountries', [adminController::class, 'AddNewCountries']);
Route::post('AddMultipleStates', [adminController::class, 'AddMultipleStates']);

// Tithe
Route::post('AddNewTithe', [MemberController::class, 'AddNewTithe']);
Route::get('Tithe/{userid}', [MemberController::class, 'GetATithe']);
Route::get('MParishTithe/{parishcode}', [MemberController::class, 'GetAllParishTithe']);
Route::post('Tithe/{userid}/update', [MemberController::class, 'UpdateTithe']);
Route::delete('MDelete/{userid}/delete', [MemberController::class, 'DeleteTithe']);

//Juveline Harvest API
Route::post('AddNewJHarvest', [MemberController::class, 'AddNewJuvelineHarvest']);
Route::get('MJuveline/{parishcode}', [MemberController::class, 'GetAllParishJuvelineDue']);
Route::get('Juveline/{userid}', [MemberController::class, 'GetAJuvelineDue']);
Route::post('Juveline/{userid}/update', [MemberController::class, 'UpdateJuvelineDue']);
Route::delete('MJuvelineDelete/{userid}/delete', [MemberController::class, 'DeleteJuvelineDue']);

//Adult Harvest API
Route::post('AddNewAdultHarvest', [MemberController::class, 'AddNewAdultHarvest']);
Route::get('MAdultHarvest/{parishcode}', [MemberController::class, 'GetAllParishAdultDue']);
Route::get('Adult/{userid}', [MemberController::class, 'GetAAdultDue']);
Route::post('Adult/{userid}/update', [MemberController::class, 'UpdateAdultDue']);
Route::delete('MAdultDelete/{userid}/delete', [MemberController::class, 'DeleteAdultDue']);

// Route::put('member/{userid}/update',[MemberController::class,'updateMember']);
// Route::delete('member/{userid}/delete',[MemberController::class,'deleteMember']);

//children API
Route::get('Allchildren', [childrenController::class, 'FetchAllChildren']);
Route::post('Addchildren', [childrenController::class, 'AddChild']);
Route::delete('children/{parentid}/{id}/delete', [childrenController::class, 'deleteChildren']);
Route::put('children/{id}/update', [childrenController::class, 'updateChild']);
Route::get('children/{parentid}/viewallchildren', [childrenController::class, 'viewchildren']);
Route::get('children/{parentid}/viewchild', [childrenController::class, 'viewchild']);

//admin API

// Route::get('Alltitle', [adminController::class, 'FetchAlltitle']);
Route::post('Addtitle', [adminController::class, 'Addnewtitle']);
Route::delete('title/{id}/delete', [adminController::class, 'deleteTitle']);
Route::put('title/{id}/update', [adminController::class, 'updateTitle']);

// add title

// Route::get('getAtitle/{Id}', [adminController::class, 'GetAtitle']);
Route::get('getAlltitle', [adminController::class, 'FetchAlltitle']);
// Route::post('updatetitle/{Id}/update', [adminController::class, 'updatetitle']);
Route::get('getTitleByGender/{gender}', [adminController::class, 'getTitleByGender']);
// Route::delete('deletetitle/{Id}/delete', [adminController::class, 'Deletetitle']);




//National
Route::get('Allnational', [adminController::class, 'FetchAllNatinal']);
Route::get('getAnational/{code}/get', [adminController::class, 'getNational']);
Route::post('Addnational', [adminController::class, 'AddNewNatinal']);
Route::put('updatenational/{code}/update', [adminController::class, 'UpdateNational']);
// Route::delete('deletenational/{code}/delete', [adminController::class, 'deleteNational']);

//State
Route::get('Allstate', [adminController::class, 'FetchAllState']);
// Route::post('Addstate',[adminController::class,'AddNewState']);
Route::get('State/{scode}', [adminController::class, 'GetAState']);
Route::put('updatestate/{scode}/update', [adminController::class, 'UpdateState']);
// Route::delete('deletestate/{scode}/delete', [adminController::class, 'deleteState']);

//area
Route::get('Allarea', [adminController::class, 'FetchAllarea']);
Route::get('Area/{acode}', [adminController::class, 'GetAnArea']);
Route::put('updatearea/{acode}/update', [adminController::class, 'UpdateArea']);
Route::delete('deletearea/{acode}/delete', [adminController::class, 'deleteArea']);

//province
Route::get('Allprovince', [adminController::class, 'FetchAllProvince']);
Route::get('Province/{pcode}', [adminController::class, 'GetAProvince']);
Route::put('Update/{pcode}/update', [adminController::class, 'UpdateProvince']);
Route::delete('Deleteprovince/{pcode}/delete', [adminController::class, 'DeleteProvince']);

//circuit
Route::get('Allcircuit', [adminController::class, 'FetchAllCircuit']);
Route::get('Circuit/{cicode}', [adminController::class, 'GetACircuit']);
Route::put('UpdateCicuit/{cicode}/update', [adminController::class, 'UpdateCircuit']);
// Route::delete('DeleteCircuit/{cicode}/delete', [adminController::class, 'DeleteCircuit']);


//District
// Route::post('AddDistrict',[adminController::class,'AddNewDistrict']);
Route::get('AllDistrict', [adminController::class, 'FetchAllDistrict']);
Route::get('District/{dcode}', [adminController::class, 'GetADistrict']);
Route::put('UpdateDistrict/{dcode}/update', [adminController::class, 'UpdateDistrict']);
// Route::delete('DeleteDistrict/{dcode}/delete', [adminController::class, 'DeleteDistrict']);

//Parish
Route::post('AddParish', [adminController::class, 'AddNewParish']);
Route::get('AllParish', [adminController::class, 'FetchAllParish']);
Route::get('Parish/{picode}', [adminController::class, 'GetAParish']);
Route::put('UpdateParish/{picode}/update', [adminController::class, 'UpdateParish']);
Route::delete('DeleteParish/{picode}/delete', [adminController::class, 'DeleteParish']);
Route::get('getParishByState/{state}', [adminController::class, 'getParishByStatename']);



//get all parishes or one parish
Route::get('getAllParishes', [adminController::class, 'fetchAllParishes']);
Route::get('getAParish/{parishcode}', [adminController::class, 'FetchAllParishes']);
Route::post('deleteAParish', [adminController::class, 'deleteAparish']);
Route::post('updateAparish', [adminController::class, 'updateAparish']);
// EVENT END POINT
Route::post('AddEvent', [adminController::class, 'AddNewEvent']);
Route::get('AllEvent', [adminController::class, 'FetchAllEvent']);
Route::get('Event/{EventId}', [adminController::class, 'GetAnEvent']);
Route::post('updateEvent/{EventId}/update', [adminController::class, 'updateEvent']);
Route::delete('DeleteEvent/{EventId}/delete', [adminController::class, 'DeleteEvent']);

// VINEYARD END POINT
Route::post('AddVineyard', [adminController::class, 'AddNewVineyard']);
Route::get('AllVineyard', [adminController::class, 'FetchAllVineyard']);
//Route::get('Vineyard/{Id}',[adminController::class,'GetAnEvent']);
Route::post('updateVineyard/{Id}/update', [adminController::class, 'updateVineyard']);
Route::delete('DeleteVineyard/{Id}/delete', [adminController::class, 'DeleteVineyard']);

// MINISTRY END POINT
Route::post('AddMinistry', [adminController::class, 'AddNewMinistry']);
Route::get('AllMinistry', [adminController::class, 'FetchAllMinistry']);
//Route::get('Vineyard/{Id}',[adminController::class,'GetAnEvent']);
Route::post('updateMinistry', [adminController::class, 'updateMinistry']);
Route::delete('DeleteMinistry/{Id}/delete', [adminController::class, 'DeleteMinistry']);

// VISITOR END POINT
Route::post('AddVisitor', [adminController::class, 'AddNewVisitor']);
Route::get('AllVisitor', [adminController::class, 'FetchAllVisitor']);
Route::get('Visitor/{Id}', [adminController::class, 'GetAVisitor']);
// Route::delete('DeleteMinistry/{Id}/delete',[adminController::class,'DeleteMinistry']);

//get All country
Route::get('GetCountries', [adminController::class, 'fetchCountries']);


//Committee
//add committee to parish
Route::post('addcommittee', [adminController::class, 'addCommittee']);
Route::get('getACommittee/{Id}', [adminController::class, 'GetACommittee']);
Route::get('getAllCommittee', [adminController::class, 'FetchAllCommittee']);
Route::get('getCommittee/{pcode}', [adminController::class, 'FetchCommittee']);
Route::post('updateCommitee/{Id}/update', [adminController::class, 'updateCommittee']);
Route::delete('deleteCommittee/{Id}/delete', [adminController::class, 'DeleteCommittee']);

//add committee Member to parish
Route::post('addcommitteeMember', [adminController::class, 'addCommitteeMember']);
Route::get('getACommitteeMember/{Id}', [adminController::class, 'GetACommitteeMember']);
Route::get('getAllCommitteeMember', [adminController::class, 'FetchAllCommitteeMember']);
Route::post('updateCommiteeMember/update', [adminController::class, 'updateCommitteeMember']);
Route::delete('deleteCommitteeMember/{Id}/delete', [adminController::class, 'deleteCommitteeMember']);


//payment
Route::post('addCommitteePayment', [MemberController::class, 'addCommitteePayment']);
Route::get('getAMemberPaymentsForAllcommittees/{memberId}', [MemberController::class, 'GetACommitteeMemberPayment']); // Member payments in all committes
Route::get('getAMemberPaymentForAcommitte/{memberId}/{committeRefno}', [MemberController::class, 'GetMemberPymtforACommitee']); // Member payments in one committes

Route::get('getACommitteeNamePayment/{Id}', [MemberController::class, 'GetACommitteeNamePayment']);
Route::post('changeCommitteePayment', [MemberController::class, 'changeCommitteePayment']);

//baptism
Route::get('fetchAllBaptismRecords', [MemberController::class, 'fetchAllBaptismRecords']);
Route::get('fetchBaptismRecord', [MemberController::class, 'fetchBaptismRecord']);
Route::post('AddBaptismRecord', [MemberController::class, 'AddBaptismRecord']);
Route::post('AddSchedule/{ChurchId}', [AdminController::class, 'AddSchedule']);

Route::post('AddAnnouncement/{ChurchId}', [AdminController::class, 'AddAnnouncement']);

Route::post('AddDepartment/{ChurchId}', [AdminController::class, 'AddDepartment']);

//allpayments
Route::get('/all-payments', [AdminController::class, 'getAllPayments']);
Route::get('getMemberPayment/{Id}', [AdminController::class, 'getMemberPayment']);
Route::get('getChurchPayment/{ChurchId}', [AdminController::class, 'getChurchPayment']);
Route::get('getSchedule/{ChurchId}', [AdminController::class, 'getSchedule']);
Route::get('getAnnouncement/{ChurchId}', [AdminController::class, 'getAnnouncement']);
Route::get('getDepartment/{ChurchId}', [AdminController::class, 'getDepartment']);


//newpayments
Route::post('AddNewOffering', [MemberController::class, 'AddNewOffering']);
Route::post('AddNewbuildingLevy', [MemberController::class, 'AddNewbuildingLevy']);
Route::post('AddNewBaptismPayment', [MemberController::class, 'AddNewBaptismPayment']);

