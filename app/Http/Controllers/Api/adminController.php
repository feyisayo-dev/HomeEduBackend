<?php

namespace App\Http\Controllers\Api;

use App\Models\area;
use App\Models\event;
use App\Models\state;
use App\Models\title;
use App\Models\member;
use App\Models\parish;
use App\Models\region;
use App\Models\circuit;
use App\Models\district;
use App\Models\ministry;
use App\Models\national;
use App\Models\province;
use App\Models\schedule;
use App\Models\vineyard;
use App\Models\visitors;
use App\Models\committee;
use App\Models\department;
use App\Models\announcement;
use Illuminate\Http\Request;
use App\Models\committeemember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use WisdomDiala\Countrypkg\Models\Country;
use Illuminate\Http\ResponseTrait\original;
use App\Http\Controllers\Api\MemberController;
use WisdomDiala\Countrypkg\Models\State as countrystate;

class adminController extends Controller
{
    public function FetchAlltitle()
    {
        $alltitle = title::all();
        if ($alltitle->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'titles' => $alltitle,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No title records found!',
            ], 200);
        }
    }

    public function Addnewtitle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gender' => 'required|string|max:191',
            'title' => 'required|string|max:191',
            'status' => 'required|string|max:191',
            'level' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $title = title::create([
                'gender' => $request->gender,
                'title' => $request->title,
                'status' => $request->status,
                'level' => $request->level,
                'p1' => $request->p1,
                'p2' => $request->p2,
                'p3' => $request->p3,
                'p4' => $request->p4,
                'p5' => $request->p5,
                'p6' => $request->p6,
                'p7' => $request->p7,
                'p8' => $request->p8,
                'p9' => $request->p9,
            ]);

            if ($title) {
                return response()->json([
                    'status' => 200,
                    'message' => $request->title . ' added sucessfully',
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong ' . $request->title . ' title not added',
                ], 200);
            }
        }
    }

    public function addCommittee(Request $request)
    {

        error_log(json_encode($request->all()));

        $validator = Validator::make($request->all(), [
            'committename' => 'required|string|max:191',
            'parishcode' => 'required|string|max:191',
            'from' => 'required|date|max:191',
            'to' => 'required|date|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            //timestamp for committeRefno
            $currentDate = date('YmdHis');
            // get committeRefno with parishcode
            $committeRefno = $request->parishcode . $currentDate;

            $fetchparish = self::FetchAllParishes($request->parishcode);
            if (!$fetchparish) {

                return response()->json([
                    'status' => 500,
                    'message' => 'Parish does not exist',
                ], 200);
            }

            //decode to get parish name
            $decode_Parish = self::decodeParishName($fetchparish);

            $parishName = $decode_Parish['parishname'];
            $verifyCommitteName = committee::where('committeName', '=', $request->committename)
                ->where('parishcode', '=', $request->parishcode)
                ->first();
            if (!$verifyCommitteName) {
                if ($verifyCommitteName) {
                    return response()->json([
                        'status' => 200,
                        'message' => $request->committename . ' for ' . $parishName . ' already created',
                    ], 200);
                } else {

                    $committeeCreateResponse = committee::create([
                        'committeRefno' => $committeRefno,
                        'committeName' => $request->committename,
                        'parishcode' => $request->parishcode,
                        'parishname' => $parishName,
                        'from' => $request->from,
                        'to' => $request->to,
                    ]);

                    if ($committeeCreateResponse) {
                        return response()->json([
                            'status' => 200,
                            'message' => $request->committename . ' committe for ' . $parishName . '  created sucessfully',
                            'data' => $committeeCreateResponse,
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => 500,
                            'message' => $request->committename . ' for ' . $parishName . ' not   created',
                        ], 200);
                    }
                }
            }
        }
    }

    public function GetACommittee($committeRefno)
    {
        $committee = committee::where('committeRefno', '=', $committeRefno)->first();
        if ($committee) {
            return response()->json([
                'status' => 200,
                'message' => $committeRefno . ' Record fetched successfully',
                'committee' => $committee,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Committee Name not found',
            ], 404);
        }
    }

    public function FetchCommittee($parishcode)
    {
        $committee = committee::where('parishcode', '=', $parishcode)->get();
        if ($committee) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'committee' => $committee,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Committee(s) not found',
            ], 404);
        }
    }


    public function FetchAllCommittee()
    {
        $allcommittee = committee::all();
        $committeCount = $allcommittee->count();
        $page = 1;
        $pageSize = 10;
        $totalPages = ceil($committeCount / $pageSize);
        if ($committeCount > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'All Committee Record fetched successfully',
                'Allcommittee' => $allcommittee,
                'totalParish' => $committeCount,
                'totalPages' => $totalPages,
                'page' => $page,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No committee records found!',
            ], 200);
        }
    }
    public function updateCommittee(Request $request, String $committeRefno)
    {
        $validator = Validator::make($request->all(), [
            'committeName' => 'required|string|max:191',
            'from' => 'required|date|max:191',
            'to' => 'required|date|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }
        $committee = committee::where('committeRefno', '=', $committeRefno)->first();

        if ($committee) {
            $committee->update([
                'committeName' => $request->committeName,
                'from' => $request->from,
                'to' => $request->to,

            ]);
            return response()->json([
                'status' => 200,
                'message' => $request->committeName . ' committee information updated Sucessfully !',
                'committee' => $committee,
            ], 200);
        } else {

            return response()->json([
                'status' => 500,
                'message' => 'Update failed as ' . $request->committeName . ' committee is not found',
            ], 200);
        }
    }

    public function DeleteCommittee(Request $request, $committeRefno)
    {

        $committee = committee::where('committeRefno', '=', $committeRefno)->first();
        //return($committee->committeName);
        if ($committee) {

            $committee->delete();

            return response()->json([
                'status' => 200,
                'message' => $committee->committeName . ' committee deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => $committee->committeName . ' Commitee not found',
            ], 404);
        }
    }

    public function addCommitteeMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'committeRefno' => 'required|string',
            'committeName' => 'required|string',
            'chairman' => 'required|string',
            'chairperson' => 'nullable|string',
            'secretary' => 'required|string',
            'Fsecretary' => 'nullable|string',
            'treasurer' => 'required|string',
            'Mmembers' => 'required|string',
            'Fmembers' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $committee = committee::where('committeRefno', '=', $request->committeRefno)->first();

            if (!$committee) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Committee Name not found !!',
                ], 422);
            }
            $committeeName = $committee['committeName'];



            $committeeMemberCreateResponse = committeemember::create([
                'committeRefno' => $request->committeRefno,
                'committeName' => $committeeName,
                'chairman' => $request->chairman,
                'chairperson' => $request->chairperson,
                'secretary' => $request->secretary,
                'Fsecretary' => $request->Fsecretary,
                'treasurer' => $request->treasurer,
                'Mmembers' => $request->Mmembers,
                'Fmembers' => $request->Fmembers,
            ]);
            if ($committeeMemberCreateResponse) {
                return response()->json([
                    'status' => 200,
                    'message' => $committeeName . ' created successfully',
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => $committeeName . ' not successfully as  created ',
                ], 200);
            }
        }
    }

    public function GetACommitteeMember($memberId)
    {
        $comittemember = committeemember::where('memberId', '=', $memberId)->first();
        if ($comittemember) {
            return response()->json([
                'status' => 200,
                'message' => $memberId . ' Record fetched successfully',
                'comittemember ' => $comittemember,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'comittemember Name not found',
            ], 404);
        }
    }

    public function FetchAllCommitteeMember()
    {
        $comittemember = committeemember::all();
        if ($comittemember->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'All Committee Record fetched successfully',
                'committeeMembers' => $comittemember,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No Commitee Members records found!',
            ], 200);
        }
    }

    public function updateCommitteeMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'committeRefno' => 'required|string',
            'committeName' => 'required|string',
            'chairman' => 'required|string',
            'chairperson' => 'nullable|string',
            'secretary' => 'required|string',
            'Fsecretary' => 'nullable|string',
            'treasurer' => 'required|string',
            'Mmembers' => 'required|string',
            'Fmembers' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $committee = committee::where('committeRefno', '=', $request->committeRefno)->first();

            if (!$committee) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Committee Name not found !!',
                ], 422);
            }
            $committeeName = $committee['committeName'];



            if ($committee) {
                $committee->update([
                    'committeRefno' => $request->committeRefno,
                    'committeName' => $committeeName,
                    'chairman' => $request->chairman,
                    'chairperson' => $request->chairperson,
                    'secretary' => $request->secretary,
                    'Fsecretary' => $request->Fsecretary,
                    'treasurer' => $request->treasurer,
                    'Mmembers' => $request->Mmembers,
                    'Fmembers' => $request->Fmembers,
                ]);
                if ($committee) {
                    return response()->json([
                        'status' => 200,
                        'message' => $committeeName . ' updated successfully',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 500,
                        'message' => $committeeName . ' not successfully updated',
                    ], 200);
                }
            }
        }
    }


    public function deleteCommitteeMember($committeRefno)
    {
        $committeemember = committeemember::where('committeRefno', '=', $committeRefno)->first();
        if ($committeemember) {

            $committeemember->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Committee members deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'title not found',
            ], 404);
        }
    }
    public function getTitleByGender($gender)
    {

        $titles = title::where('gender', '=', $gender)->get();
        if ($titles) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'titles' => $titles,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function deleteTitle($id)
    {

        $title = title::where('id', '=', $id)->first();
        if ($title) {

            $title->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Title/Anointing deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'title not found',
            ], 404);
        }
    }

    public function updateTitle(Request $request, Int $id)
    {
        error_log('get here');


        $validator = Validator::make($request->all(), [
            'gender' => 'required|string|max:191',
            'title' => 'required|string|max:191',
            'status' => 'required|string|max:191',
            'level' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $title = title::where('id', '=', $id)->first();

            if ($title) {
                $title->update([
                    'gender' => $request->gender,
                    'title' => $request->title,
                    'status' => $request->status,
                    'level' => $request->level,
                    'p1' => $request->p1,
                    'p2' => $request->p2,
                    'p3' => $request->p3,
                    'p4' => $request->p4,
                    'p5' => $request->p5,
                    'p6' => $request->p6,
                    'p7' => $request->p7,
                    'p8' => $request->p8,
                    'p9' => $request->p9,

                ]);
                return response()->json([
                    'status' => 200,
                    'message' => 'Title information updated Sucessfully !',
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed as titlt is not found',
                ], 200);
            }
        }
    }

    public function FetchAllNatinal()
    {
        // Fetch all national records and eager load their associated state records
        $nationals = National::with('state.area.province.circuit.district.parish', 'area.province.circuit.district.parish', 'province.circuit.district.parish', 'circuit.district.parish', 'district.parish', 'parish')->get();

        if ($nationals->count() > 0) {
            return response()->json([
                // 'status' => 200,
                // 'message' => 'Record fetched successfully',
                'nationalsParish ' => $nationals,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No title records found!',
            ], 200);
        }
    }

    public function getNational($nationalcode)
    {
        // Fetch one national records and eager load their associated state records
        $nationals = National::with('state.area.province.circuit.district.parish', 'area.province.circuit.district.parish', 'province.circuit.district.parish', 'circuit.district.parish', 'district.parish', 'parish')->where('code', $nationalcode)->get();

        if ($nationals->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'nationalParish ' => $nationals,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No title records found!',
            ], 200);
        }
    }

    private function AddNationalParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $parishCategory)
    {
        $countryDetails = country::where('id', $country)->first();
        $code = strtoupper(substr($countryDetails->name, 0, 3));

        $countNational = national::where('code', 'LIKE', '%' . $code . '%')->count();

        if ($countNational == 0) {
            $countNational = 1;
            $num_padded = sprintf("%02d", $countNational);
        } elseif ($countNational < 10) {
            $countNational = $countNational + 1;
            $num_padded = sprintf("%02d", $countNational);
        } else {
            $num_padded = $countNational + 1;
        }

        $national = national::create([
            'nationalname' => $parishName,
            'email' => $email,
            'phone1' => $phone1,
            'phone2' => $phone2,
            'country' => $countryDetails->name,
            'states' => $parishState,
            'city' => $city,
            'address' => $address,
            'code' => $code . $num_padded,
            'category' => $parishCategory,
        ]);

        if ($national) {
            return response()->json([
                'status' => 200,
                'message' => 'National parish added sucessfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong title not added',
            ], 200);
        }
    }

    private function addStateParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory)
    {

        $countryDetails = country::where('id', $country)->first();
        // $CountryCode = strtoupper(substr($countryDetails->name, 0, 3));

        $countState = state::where('state', 'LIKE', '%' . $parishState . '%')->count();

        $scode = strtoupper(substr($parishState, 0, 2));

        if ($countState == 0) {
            $countState = 1;
            $num_padded = sprintf("%02d", $countState);
        } elseif ($countState < 10) {
            $countState = $countState + 1;
            $num_padded = sprintf("%02d", $countState);
        } else {
            $num_padded = $countState + 1;
        }

        $state = state::create([
            'email' => $email,
            'phone1' => $phone1,
            'phone2' => $phone2,
            'country' => $countryDetails->name,
            'state' => $parishState,
            'city' => $city,
            'address' => $address,
            'statename' => $parishName,
            'nationalcode' => $reportTo,
            'scode' => $scode . $num_padded,
            'category' => $parishCategory,
        ]);

        if ($state) {
            return response()->json([
                'status' => 200,
                'message' => 'State parish added sucessfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong State parish not added',
            ], 200);
        }
    }

    private function addRegionParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory)
    {

        $countryDetails = country::where('id', $country)->first();
        // $CountryCode = strtoupper(substr($countryDetails->name, 0, 3));

        $countRegion = region::where('state', 'LIKE', '%' . $parishState . '%')->count();

        $scode = strtoupper(substr($parishState, 0, 2));

        if ($countRegion == 0) {
            $countRegion = 1;
            $num_padded = sprintf("%02d", $countRegion);
        } elseif ($countRegion < 10) {
            $countRegion = $countRegion + 1;
            $num_padded = sprintf("%02d", $countRegion);
        } else {
            $num_padded = $countRegion + 1;
        }

        $region = region::create([
            'email' => $email,
            'phone1' => $phone1,
            'phone2' => $phone2,
            'country' => $countryDetails->name,
            'state' => $parishState,
            'city' => $city,
            'address' => $address,
            'regionname' => $parishName,
            'nationalcode' => $reportTo,
            'rcode' => $scode . $num_padded,
            'category' => $parishCategory,
        ]);


        if ($region) {
            return response()->json([
                'status' => 200,
                'message' => 'Region parish added sucessfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong Region parish not added',
            ], 200);
        }
    }


    public function addAreaParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory)
    {
        $countryDetails = country::where('id', $country)->first();
        // $CountryCode = strtoupper(substr($countryDetails->name, 0, 3));

        $countState = area::where('state', 'LIKE', '%' . $parishState . '%')->count();

        $scode = strtoupper(substr($parishState, 0, 2));

        if ($countState == 0) {
            $countState = 1;
            $num_padded = sprintf("%02d", $countState);
        } elseif ($countState < 10) {
            $countState = $countState + 1;
            $num_padded = sprintf("%02d", $countState);
        } else {
            $num_padded = $countState + 1;
        }

        $area = area::create([
            'email' => $email,
            'phone1' => $phone1,
            'phone2' => $phone2,
            'country' => $countryDetails->name,
            'state' => $parishState,
            'city' => $city,
            'address' => $address,
            'areaname' => $parishName,
            'reportingcode' => $reportTo,
            'acode' => $scode . $num_padded,
            'category' => $parishCategory,
        ]);

        if ($area) {
            return response()->json([
                'status' => 200,
                'message' => 'Area parish added sucessfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong State parish not added',
            ], 200);
        }
    }

    public function addProvinceParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory)
    {

        $countryDetails = country::where('id', $country)->first();
        // $CountryCode = strtoupper(substr($countryDetails->name, 0, 3));
        $countState = province::where('state', 'LIKE', '%' . $parishState . '%')->count();

        $scode = strtoupper(substr($parishState, 0, 2));

        if ($countState == 0) {
            $countState = 1;
            $num_padded = sprintf("%02d", $countState);
        } elseif ($countState < 10) {
            $countState = $countState + 1;
            $num_padded = sprintf("%02d", $countState);
        } else {
            $num_padded = $countState + 1;
        }

        $province = province::create([
            'email' => $email,
            'phone1' => $phone1,
            'phone2' => $phone2,
            'country' => $countryDetails->name,
            'state' => $parishState,
            'city' => $city,
            'address' => $address,
            'provincename' => $parishName,
            'reportingcode' => $reportTo,
            'pcode' => $scode . $num_padded,
            'category' => $parishCategory,
        ]);

        if ($province) {
            return response()->json([
                'status' => 200,
                'message' => 'Province parish added sucessfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong State parish not added',
            ], 200);
        }
    }

    public function addCircuitParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory)
    {

        $countryDetails = country::where('id', $country)->first();
        // $CountryCode = strtoupper(substr($countryDetails->name, 0, 3));
        $countState = circuit::where('state', 'LIKE', '%' . $parishState . '%')->count();

        $scode = strtoupper(substr($parishState, 0, 2));

        if ($countState == 0) {
            $countState = 1;
            $num_padded = sprintf("%02d", $countState);
        } elseif ($countState < 10) {
            $countState = $countState + 1;
            $num_padded = sprintf("%02d", $countState);
        } else {
            $num_padded = $countState + 1;
        }

        $circuit = circuit::create([
            'email' => $email,
            'phone1' => $phone1,
            'phone2' => $phone2,
            'country' => $countryDetails->name,
            'state' => $parishState,
            'city' => $city,
            'address' => $address,
            'circuitname' => $parishName,
            'reportingcode' => $reportTo,
            'cicode' => $scode . $num_padded,
            'category' => $parishCategory,
        ]);

        if ($circuit) {
            return response()->json([
                'status' => 200,
                'message' => 'Circuit parish added sucessfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong State parish not added',
            ], 200);
        }
    }

    public function addDistrictParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory)
    {

        $countryDetails = country::where('id', $country)->first();
        // $CountryCode = strtoupper(substr($countryDetails->name, 0, 3));
        $countState = district::where('state', 'LIKE', '%' . $parishState . '%')->count();

        $scode = strtoupper(substr($parishState, 0, 2));

        if ($countState == 0) {
            $countState = 1;
            $num_padded = sprintf("%02d", $countState);
        } elseif ($countState < 10) {
            $countState = $countState + 1;
            $num_padded = sprintf("%02d", $countState);
        } else {
            $num_padded = $countState + 1;
        }

        $district = district::create([
            'email' => $email,
            'phone1' => $phone1,
            'phone2' => $phone2,
            'country' => $countryDetails->name,
            'state' => $parishState,
            'city' => $city,
            'address' => $address,
            'districtname' => $parishName,
            'reportingcode' => $reportTo,
            'dcode' => $scode . $num_padded,
            'category' => $parishCategory,
        ]);

        if ($district) {
            return response()->json([
                'status' => 200,
                'message' => 'District parish added sucessfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong State parish not added',
            ], 200);
        }
    }

    public function addParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory)
    {

        $countryDetails = country::where('id', $country)->first();
        // $CountryCode = strtoupper(substr($countryDetails->name, 0, 3));
        $countState = parish::where('state', 'LIKE', '%' . $parishState . '%')->count();

        $scode = strtoupper(substr($parishState, 0, 2));

        if ($countState == 0) {
            $countState = 1;
            $num_padded = sprintf("%02d", $countState);
        } elseif ($countState < 10) {
            $countState = $countState + 1;
            $num_padded = sprintf("%02d", $countState);
        } else {
            $num_padded = $countState + 1;
        }

        $parish = parish::create([
            'email' => $email,
            'phone1' => $phone1,
            'phone2' => $phone2,
            'country' => $countryDetails->name,
            'state' => $parishState,
            'city' => $city,
            'address' => $address,
            'parishname' => $parishName,
            'reportingcode' => $reportTo,
            'picode' => $scode . $num_padded,
            'category' => $parishCategory,
        ]);

        if ($parish) {
            return response()->json([
                'status' => 200,
                'message' => 'Parish parish added sucessfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong State parish not added',
            ], 200);
        }
    }

    public function UpdateNational(Request $request, string $code)
    {
        $data = $request->postData;

        $validator = Validator::make($data, [
            'email' => 'required|string|max:191',
            'phone1' => 'required|string|max:191',
            'country' => 'required|string|max:191',
            'state' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'name' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            $nationalParish = national::where('code', '=', $code)->first();

            if ($nationalParish) {
                $nationalParish->update([
                    'email' => $data['email'],
                    'phone1' => $data['phone1'],
                    'phone2' => $data['phone2'],
                    'country' => $data['country'],
                    'states' => $data['state'],
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'nationalname' => $data['name'],
                ]);
                return response()->json([
                    'status' => 200,
                    'message' => 'National Parish information updated Sucessfully!',
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed as national title is not found',
                ], 200);
            }
        }
    }

    public function deleteNational($code)
    {

        $national = national::where('code', '=', $code)->first();
        if ($national) {

            $national->delete();
            return response()->json([
                'status' => 200,
                'message' => 'National parish deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'national not found',
            ], 404);
        }
    }

    public function FetchAllState()
    {
        $state = state::with('area.province.circuit.district.parish', 'province.circuit.district.parish', 'circuit.district', 'district.parish', 'parish')->get();

        if ($state->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'State Record fetched successfully',
                'StateParish ' => $state,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No State records found!',
            ], 200);
        }
    }

    public function GetAState($scode)
    {
        $state = state::with('area.province.circuit.district.parish', 'province.circuit.district.parish', 'circuit.district', 'district.parish', 'parish')->where('scode', $scode)->get();
        if ($state) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'state ' => $state,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function UpdateState(Request $request, string $scode)
    {
        $data = $request->postData;

        $validator = Validator::make($data, [
            'email' => 'required|string|max:191',
            'phone1' => 'required|string|max:191',
            'country' => 'required|string|max:191',
            'state' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'name' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            $StateParish = state::where('scode', '=', $scode)->first();

            if ($StateParish) {
                $StateParish->update([
                    'email' => $data['email'],
                    'phone1' => $data['phone1'],
                    'phone2' => $data['phone2'],
                    'country' => $data['country'],
                    'state' => $data['state'],
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'statename' => $data['name'],
                    'nationalcode' => $data['reportTo'],
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => $data['name'] . ' information updated successfully!',
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed as ' . $data['name'] . ' Parish is not found',
                ], 200);
            }
        }
    }

    public function deleteState($scode)
    {

        $state = state::where('scode', '=', $scode)->first();
        if ($state) {

            $state->delete();
            return response()->json([
                'status' => 200,
                'message' => $state->statename . ' deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Data not found',
            ], 404);
        }
    }


    public function deleteRegion($rcode)
    {
        $region = region::where('rcode', '=', $rcode)->first();
        if ($region) {

            $region->delete();
            return response()->json([
                'status' => 200,
                'message' => $region->regionname . ' deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => $region->regionname . ' not found',
            ], 404);
        }
    }
    public function FetchAllarea()
    {
        //   $area = area::all();
        $area = area::with('province.circuit.district.parish', 'circuit.district.parish', 'district.parish', 'parish')->get();
        if ($area->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'areaParish ' => $area,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No title records found!',
            ], 200);
        }
    }

    public function GetAnArea($acode)
    {
        //    $state = state::where('scode', '=', $scode)->first();
        // $state = state::find($scode)->with('national')->get();
        $area = area::with('province.circuit.district.parish', 'circuit.district.parish', 'district.parish', 'parish')->where('acode', $acode)->get();

        if ($area) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'area ' => $area,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function UpdateRegion(Request $request, string $rcode)
    {
        $data = $request->postData;

        // Validate the extracted data
        $validator = Validator::make($data, [
            // Validator for updating region
            'email' => 'required|string|max:191',
            'phone1' => 'required|string|max:191',
            'country' => 'required|string|max:191',
            'state' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'reportTo' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $RegionParish = region::where('rcode', '=', $rcode)->first();

            if ($RegionParish) {
                $RegionParish->update([
                    // Payload for updating the region
                    'email' =>  $data['email'],
                    'phone1' =>  $data['phone1'],
                    'phone2' =>  $data['phone2'],
                    'country' =>  $data['country'],
                    'state' =>  $data['state'],
                    'city' =>  $data['city'],
                    'address' =>  $data['address'],
                    'regionname' =>  $data['name'],
                    'reportingcode' =>  $data['reportTo'],
                ]);
                return response()->json([
                    'status' => 200,
                    'message' =>  $data['name'] . ' information updated Successfully!',
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed: ' .  $data['name'] . ' Region not found',
                ], 200);
            }
        }
    }


    public function UpdateArea(Request $request, string $acode)
    {
        $data = $request->postData;

        $validator = Validator::make($data, [
            'email' => 'required|string|max:191',
            'phone1' => 'required|string|max:191',
            'country' => 'required|string|max:191',
            'state' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'reportTo' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $AreaParish = area::where('acode', '=', $acode)->first();

            if ($AreaParish) {
                $AreaParish->update([
                    //Copy payload from Adding New State Minus acode
                    'email' =>  $data['email'],
                    'phone1' =>  $data['phone1'],
                    'phone2' =>  $data['phone2'],
                    'country' =>  $data['country'],
                    'state' =>  $data['state'],
                    'city' =>  $data['city'],
                    'address' =>  $data['address'],
                    'areaname' =>  $data['name'],
                    'reportingcode' =>  $data['reportTo'],

                ]);
                return response()->json([
                    'status' => 200,
                    'message' =>  $data['name'] . 'information updated Sucessfully !',
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed ' .  $data['name'] . ' Parish is not found',
                ], 200);
            }
        }
    }

    public function deleteArea($acode)
    {

        $area = area::where('acode', '=', $acode)->first();
        if ($area) {

            $area->delete();
            return response()->json([
                'status' => 200,
                'message' => $area->areaname . ' deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => $area->areaname . ' not found',
            ], 404);
        }
    }

    public function FetchAllProvince()
    {

        $Province = province::with('circuit.district.parish', 'district.parish', 'parish')->get();
        //   $Province = Province::all();
        if ($Province->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'ProvinceParish ' => $Province,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No title records found!',
            ], 200);
        }
    }
    public function GetAProvince($pcode)
    {
        // $province = province::where('pcode', $pcode)
        //     ->leftJoin('area', 'province.reportingcode', '=', 'area.acode')
        //     ->leftJoin('state', function ($joinState) {
        //         $joinState->on('area.reportingcode', '=', 'state.scode')
        //             ->orOn('province.reportingcode', '=', 'state.scode');
        //     })
        //     ->leftJoin('national', function ($join) {
        //         $join->on('state.nationalcode', '=', 'national.code')
        //             ->orOn('area.reportingcode', '=', 'national.code')
        //             ->orOn('province.reportingcode', '=', 'national.code');
        //     })
        //     ->select(
        //         'province.*',
        //         'state.statename',
        //         'state.scode as statecode',
        //         'area.areaname',
        //         'area.acode as areacode',
        //         'national.nationalname as nationalname',
        //         'national.code as nationalcode'
        //     )
        //     ->first();

        $province = province::with('circuit.district.parish', 'district.parish', 'parish')->where('pcode', $pcode)->get();
        if ($province) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'province' => $province,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Province not found',
            ], 404);
        }
    }

    public function UpdateProvince(Request $request, string $pcode)
    {
        $data = $request->postData;

        $validator = Validator::make($data, [
            'email' => 'required|string|max:191',
            'phone1' => 'required|string|max:191',
            'country' => 'required|string|max:191',
            'state' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'reportTo' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $provinceParish = province::where('pcode', '=', $pcode)->first();

            if ($provinceParish) {
                $provinceParish->update([
                    //Copy payload from Adding New Province Minus pcode
                    'email' =>  $data['email'],
                    'phone1' =>  $data['phone1'],
                    'phone2' =>  $data['phone2'],
                    'country' =>  $data['country'],
                    'state' =>  $data['state'],
                    'city' =>  $data['city'],
                    'address' =>  $data['address'],
                    'provincename' =>  $data['name'],
                    'reportingcode' =>  $data['reportTo'],

                ]);
                return response()->json([
                    'status' => 200,
                    'message' =>  $data['name'] . 'information updated Sucessfully !',
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed ' .  $data['name'] . ' Parish is not found',
                ], 200);
            }
        }
    }

    public function DeleteProvince($pcode)
    {

        $province = province::where('pcode', '=', $pcode)->first();
        if ($province) {

            $province->delete();
            return response()->json([
                'status' => 200,
                'message' => $province->provincename . ' deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => $province->provincename . ' not found',
            ], 404);
        }
    }

    public function FetchAllCircuit()
    {

        // $circuit = circuit::all();
        $circuit = circuit::with('district.parish', 'parish')->get();

        if ($circuit->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'circuitParish ' => $circuit,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No Circuit records found!',
            ], 200);
        }
    }

    public function GetACircuit($cicode)
    {
        // $circuit = circuit::where('cicode', $cicode)
        //     ->leftJoin('province', 'circuit.reportingcode', '=', 'province.pcode')

        //     ->leftJoin('area', function ($joinCircuit) {
        //         $joinCircuit->on('province.reportingcode', '=', 'area.acode')
        //             ->orOn('circuit.reportingcode', '=', 'area.acode');
        //     })
        //     ->leftJoin('state', function ($joinState) {
        //         $joinState->on('area.reportingcode', '=', 'state.scode')
        //             ->orOn('province.reportingcode', '=', 'state.scode')
        //             ->orOn('circuit.reportingcode', '=', 'state.scode');
        //     })
        //     ->leftJoin('national', function ($join) {
        //         $join->on('state.nationalcode', '=', 'national.code')
        //             ->orOn('area.reportingcode', '=', 'national.code')
        //             ->orOn('province.reportingcode', '=', 'national.code')
        //             ->orOn('circuit.reportingcode', '=', 'national.code');

        //     })
        //     ->select(
        //         'circuit.*',
        //         'province.provincename',
        //         'province.pcode as provincecode',
        //         'area.areaname',
        //         'area.acode as areacode',
        //         'state.statename',
        //         'state.scode as statecode',
        //         'national.nationalname as nationalname',
        //         'national.code as nationalcode'
        //     )
        //     ->first();
        $circuit = circuit::with('district.parish', 'parish')->where('cicode', $cicode)->get();

        if ($circuit) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'circuit' => $circuit,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'circuit not found',
            ], 404);
        }
    }

    public function UpdateCircuit(Request $request, string $cicode)
    {
        $data = $request->postData;


        $validator = Validator::make($data, [

            'email' => 'required|string|max:191',
            'phone1' => 'required|string|max:191',
            'country' => 'required|string|max:191',
            'state' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'reportTo' => 'required|string|max:191',
            // we dont have to add cicode bc we will generate it ourselve
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $circuitParish = circuit::where('cicode', '=', $cicode)->first();

            if ($circuitParish) {
                $circuitParish->update([
                    //Copy payload from Adding New Circuit Minus pcode
                    'email' =>  $data['email'],
                    'phone1' =>  $data['phone1'],
                    'phone2' =>  $data['phone2'],
                    'country' =>  $data['country'],
                    'state' =>  $data['state'],
                    'city' =>  $data['city'],
                    'address' =>  $data['address'],
                    'circuitname' =>  $data['name'],
                    'reportingcode' =>  $data['reportTo'],
                ]);
                return response()->json([
                    'status' => 200,
                    'message' =>  $data['name'] . ' Circuit updated Sucessfully !',
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed ' .  $data['name'] . ' Circuit is not found',
                ], 200);
            }
        }
    }

    public function DeleteCircuit($cicode)
    {

        $circuit = circuit::where('cicode', '=', $cicode)->first();
        if ($circuit) {

            $circuit->delete();
            return response()->json([
                'status' => 200,
                'message' => $circuit->circuitname . ' deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => $circuit->circuitname . ' not found',
            ], 404);
        }
    }

    public function FetchAllDistrict()
    {
        $district = district::with('parish')->get();
        if ($district->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'districtParish ' => $district,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No district records found!',
            ], 200);
        }
    }
    public function GetADistrict($dcode)
    {
        // $district = district::where('dcode', $dcode)
        //     ->leftJoin('circuit', 'district.reportingcode', '=', 'circuit.cicode')

        //     ->leftJoin('province', function ($joinProvince) {
        //         $joinProvince->on('circuit.reportingcode', '=', 'province.pcode')
        //             ->orOn('district.reportingcode', '=', 'province.pcode');
        //     })

        //     ->leftJoin('area', function ($joinArea) {
        //         $joinArea->on('province.reportingcode', '=', 'area.acode')
        //             ->orOn('circuit.reportingcode', '=', 'area.acode')
        //             ->orOn('district.reportingcode', '=', 'area.acode');
        //     })
        //     ->leftJoin('state', function ($joinState) {
        //         $joinState->on('area.reportingcode', '=', 'state.scode')
        //             ->orOn('province.reportingcode', '=', 'state.scode')
        //             ->orOn('circuit.reportingcode', '=', 'state.scode')
        //             ->orOn('district.reportingcode', '=', 'state.scode')
        //         ;
        //     })
        //     ->leftJoin('national', function ($join) {
        //         $join->on('state.nationalcode', '=', 'national.code')
        //             ->orOn('area.reportingcode', '=', 'national.code')
        //             ->orOn('province.reportingcode', '=', 'national.code')
        //             ->orOn('circuit.reportingcode', '=', 'national.code')
        //             ->orOn('district.reportingcode', '=', 'national.code');

        //     })
        //     ->select(
        //         'district.*',
        //         'circuit.circuitname',
        //         'circuit.cicode as circuitcode',
        //         'province.provincename',
        //         'province.pcode as provincecode',
        //         'area.areaname',
        //         'area.acode as areacode',
        //         'state.statename',
        //         'state.scode as statecode',
        //         'national.nationalname as nationalname',
        //         'national.code as nationalcode'
        //     )
        //     ->first();

        $district = district::with('parish')->where('dcode', $dcode)->get();

        if ($district) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'district' => $district,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'circuit not found',
            ], 404);
        }
    }

    public function UpdateDistrict(Request $request, string $dcode)
    {
        $data = $request->postData;

        // Validate the extracted data
        $validator = Validator::make($data, [
            'email' => 'required|string|max:191',
            'phone1' => 'required|string|max:191',
            'country' => 'required|string|max:191',
            'state' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'reportTo' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $district = District::where('dcode', '=', $dcode)->first();

            if ($district) {
                $district->update([
                    'email' =>  $data['email'],
                    'phone1' =>  $data['phone1'],
                    'phone2' =>  $data['phone2'],
                    'country' =>  $data['country'],
                    'state' =>  $data['state'],
                    'city' =>  $data['city'],
                    'address' =>  $data['address'],
                    'districtname' =>  $data['name'],
                    'reportingcode' =>  $data['reportTo'],
                ]);
                return response()->json([
                    'status' => 200,
                    'message' =>  $data['name'] . ' district updated successfully!',
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed: ' .  $data['name'] . ' district not found',
                ], 200);
            }
        }
    }

    public function DeleteDistrict($dcode)
    {

        $district = district::where('dcode', '=', $dcode)->first();
        if ($district) {

            $district->delete();
            return response()->json([
                'status' => 200,
                'message' => $district->districtname . ' deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => $district->districtname . ' not found',
            ], 404);
        }
    }

    //Use to add all parish from national to the lowest
    public function AddNewParish(Request $request)
    {

        $parishName = $request->postData['name'];
        $email = $request->postData['email'];
        $phone1 = $request->postData['phone1'];
        $phone2 = $request->postData['phone2'];
        $address = $request->postData['address'];
        $country = $request->postData['country'];
        $parishState = $request->postData['state'];
        $city = $request->postData['city'];
        $reportTo = $request->postData['reportTo'];
        $parishCategory = $request->postData['category'];

        if (isset($parishCategory) && ($parishCategory) == 'national') {
            //add national parish
            $status = self::AddNationalParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $parishCategory);
            return $status;
            //add state parish
        } elseif (isset($parishCategory) && ($parishCategory) == 'state') {

            $status = self::addStateParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory);
            return $status;
        } elseif (isset($parishCategory) && ($parishCategory) == 'region') {



            $status = self::addRegionParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory);
            return $status;
        } elseif (isset($parishCategory) && ($parishCategory) == 'area') {
            $status = self::addAreaParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory);
            return $status;
        } elseif (isset($parishCategory) && ($parishCategory) == 'province') {
            $status = self::addProvinceParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory);
            return $status;
        } elseif (isset($parishCategory) && ($parishCategory) == 'circuit') {
            $status = self::addCircuitParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory);
            return $status;
        } elseif (isset($parishCategory) && ($parishCategory) == 'district') {
            $status = self::addDistrictParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory);
            return $status;
        } else {
            $status = self::addParish($parishName, $email, $phone1, $phone2, $address, $country, $parishState, $city, $reportTo, $parishCategory);
            return $status;
        }
    }



    //Use to delete any parish from national to the lowest
    public function deleteAparish(Request $request)
    {

        $parishcode = $request->postData['parishcode'];
        $parishCategory = $request->postData['category'];

        Log::error("delete param==>" . json_encode($request->postData));

        if (isset($parishCategory) && ($parishCategory) == 'national') {
            //add national parish
            $status = self::deleteNational($parishcode);
            return $status;
            //add state parish
        } elseif (isset($parishCategory) && ($parishCategory) == 'state') {

            $status = self::deleteState($parishcode);
            return $status;
        } elseif (isset($parishCategory) && ($parishCategory) == 'region') {


            $status = self::deleteRegion($parishcode);
            return $status;
        } elseif (isset($parishCategory) && ($parishCategory) == 'area') {

            $status = self::deleteArea($parishcode);
            return $status;
        } elseif (isset($parishCategory) && ($parishCategory) == 'province') {
            $status = self::DeleteProvince($parishcode);
            return $status;
        } elseif (isset($parishCategory) && ($parishCategory) == 'circuit') {
            $status = self::DeleteCircuit($parishcode);
            return $status;
        } elseif (isset($parishCategory) && ($parishCategory) == 'district') {
            $status = self::DeleteDistrict($parishcode);
            return $status;
        } else {
            $status = self::DeleteParish($parishcode);
            return $status;
        }
    }

    public function updateAparish(Request $request)
    {
        // Log the received postData
        Log::info("Received postData: " . json_encode($request->postData));

        $parishcode = $request->postData['parishcode'];
        $parishCategory = $request->postData['category'];

        Log::error("Update param==>" . json_encode($request->postData));

        if (isset($parishCategory) && $parishCategory == 'national') {
            // Update national parish
            $status = self::UpdateNational($request, $parishcode);
            return $status;
        } elseif (isset($parishCategory) && $parishCategory == 'state') {
            // Update state parish
            $status = self::UpdateState($request, $parishcode);
            return $status;
        } elseif (isset($parishCategory) && $parishCategory == 'region') {
            // Update region parish
            $status = self::UpdateRegion($request, $parishcode);
            return $status;
        } elseif (isset($parishCategory) && $parishCategory == 'area') {
            // Update area parish
            $status = self::UpdateArea($request, $parishcode);
            return $status;
        } elseif (isset($parishCategory) && $parishCategory == 'province') {
            // Update province parish
            $status = self::UpdateProvince($request, $parishcode);
            return $status;
        } elseif (isset($parishCategory) && $parishCategory == 'circuit') {
            // Update circuit parish
            $status = self::UpdateCircuit($request, $parishcode);
            return $status;
        } elseif (isset($parishCategory) && $parishCategory == 'district') {
            // Update district parish
            $status = self::UpdateDistrict($request, $parishcode);
            return $status;
        } else {
            // Update general parish
            $status = self::UpdateParish($request, $parishcode);
            return $status;
        }
    }


    public function FetchAllParish()
    {

        // $parish = parish::all();
        // $parish = parish::with('district.circuit.province.area.state.national')->get();
        // $parish = parish::with('district.circuit.province.area.state.national','circuit.province.area.state.national','province.area.state.national','area.state.national','state.national','national')->get();

        $parish = parish::with('district', 'circuit', 'province', 'area', 'state', 'national')->get();
        //remove null from the array of event
        $filteredParish = $parish->filter(function ($item) {
            return !is_null($item);
        });

        if ($parish->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'parishParish ' => $parish,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No parish records found!',
            ], 200);
        }
    }

    public function GetAParish($picode)
    {
        $parish = parish::with('district.circuit.province.area.state.national', 'circuit.province.area.state.national', 'province.area.state.national', 'area.state.national', 'state.national', 'national')->where('picode', '=', $picode)->get();

        if ($parish) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'parish' => $parish,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'circuit not found',
            ], 404);
        }
    }

    public function UpdateParish(Request $request, string $picode)
    {

        $data = $request->postData;

        $validator = Validator::make($data, [
            'email' => 'required|string|max:191',
            'phone1' => 'required|string|max:191',
            'country' => 'required|string|max:191',
            'state' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'reportTo' => 'required|string|max:191',
            // we dont have to add picode bc we will generate it ourselve
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $parishParish = parish::where('picode', '=', $picode)->first();

            if ($parishParish) {
                $parishParish->update([
                    //Copy payload from Adding New Diostrict Minus pcode
                    'email' => $data['email'],
                    'phone1' => $data['phone1'],
                    'phone2' => $data['phone2'],
                    'country' => $data['country'],
                    'state' => $data['state'],
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'parishname' => $data['parishname'],
                    'reportingcode' => $data['reportTo'],
                    //dcode will not be included because its the one we are using
                ]);
                return response()->json([
                    'status' => 200,
                    'message' =>  $data['name'] . ' parish updated Sucessfully !',
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed ' .  $data['name'] . ' parish is not found',
                ], 200);
            }
        }
    }

    public function DeleteParish($picode)
    {

        $parish = parish::where('picode', '=', $picode)->first();
        if ($parish) {

            $parish->delete();
            return response()->json([
                'status' => 200,
                'message' => $parish->parishname . ' Parish deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => $parish->parishname . ' not found',
            ], 404);
        }
    }

    public static function fetchAllParishes($parishcode = null)
    {
        //Get All parishes
        $national = national::select('id', 'email', 'phone1', 'phone2', 'country', 'states', 'city', 'address', 'nationalname as parishname', 'code as parishcode', 'category', national::raw('null as reportingTo'));
        $state = state::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'statename as parishname', 'scode as parishcode', 'category', 'nationalcode as reportingcode');
        $region = region::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'regionname as parishname', 'rcode as parishcode', 'category', 'nationalcode as reportingcode');
        $area = area::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'areaname as parishname', 'acode as parishcode', 'category', 'reportingcode');
        $province = province::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'provincename as parishname', 'pcode as parishcode', 'category', 'reportingcode');
        $circuit = circuit::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'circuitname as parishname', 'cicode as parishcode', 'category', 'reportingcode');
        $district = district::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'districtname as parishname', 'dcode as parishcode', 'category', 'reportingcode');
        $parish = parish::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'parishname as parishname', 'picode as parishcode', 'category', 'reportingcode');

        ///To get a single parish
        if ($parishcode !== null) {
            $national = national::select('id', 'email', 'phone1', 'phone2', 'country', 'states', 'city', 'address', 'nationalname as parishname', 'code as parishcode', 'category', national::raw('null as reportingTo'))->where('code', $parishcode);
            $state = state::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'statename as parishname', 'scode as parishcode', 'category', 'nationalcode as reportingcode')->orWhere('scode', $parishcode);
            $region = region::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'regionname as parishname', 'rcode as parishcode', 'category', 'nationalcode as reportingcode')->orWhere('rcode', $parishcode);
            $area = area::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'areaname as parishname', 'acode as parishcode', 'category', 'reportingcode')->orWhere('acode', $parishcode);
            $province = province::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'provincename as parishname', 'pcode as parishcode', 'category', 'reportingcode')->orWhere('pcode', $parishcode);
            $circuit = circuit::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'circuitname as parishname', 'cicode as parishcode', 'category', 'reportingcode')->orWhere('cicode', $parishcode);
            $district = district::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'districtname as parishname', 'dcode as parishcode', 'category', 'reportingcode')->orWhere('dcode', $parishcode);
            $parish = parish::select('id', 'email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'parishname as parishname', 'picode as parishcode', 'category', 'reportingcode')->orWhere('picode', $parishcode);
        }
        $result = $national
            ->union($state)
            ->union($region)
            ->union($area)
            ->union($province)
            ->union($circuit)
            ->union($district)
            ->union($parish)
            ->get();
        $parisCount = $result->count();
        $page = 1;
        $pageSize = 10;
        $totalPages = ceil($parisCount / $pageSize);

        return response()->json([
            'status' => 200,
            'message' => 'Record fetched successfully',
            'allParish' => $result->toArray(),
            'totalParish' => $parisCount,
            'totalPages' => $totalPages,
            'page' => $page,
        ], 200);
    }

    public static function getParishByStatename($statename = null)
    {

        ///To get parish for state single parish
        if ($statename !== null) {
            $national = national::select('email', 'phone1', 'phone2', 'country', 'states', 'city', 'address', 'nationalname as parishname', 'code as parishcode')->where('states', $statename);
            $state = state::select('email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'statename as parishname', 'scode as parishcode')->orWhere('state', $statename);
            $area = area::select('email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'areaname as parishname', 'acode as parishcode')->orWhere('state', $statename);
            $province = province::select('email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'provincename as parishname', 'pcode as parishcode')->orWhere('state', $statename);
            $circuit = circuit::select('email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'circuitname as parishname', 'cicode as parishcode')->orWhere('state', $statename);
            $district = district::select('email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'districtname as parishname', 'dcode as parishcode')->orWhere('state', $statename);
            $parish = parish::select('email', 'phone1', 'phone2', 'country', 'state', 'city', 'address', 'parishname as parishname', 'picode as parishcode')->orWhere('state', $statename);
        }

        $result = $national
            ->union($state)
            ->union($area)
            ->union($province)
            ->union($circuit)
            ->union($district)
            ->union($parish)
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'Record fetched successfully',
            'Allparish' => $result->toArray(),
        ], 200);
    }

    public function AddNewEvent(Request $request)
    {
        Log::info("Received postData: " . json_encode($request->all()));

        $validator = Validator::make($request->all(), [
            'Title' => 'required|string|max:191',
            'Description' => 'nullable|string|max:191',
            'startdate' => 'required|date_format:Y-m-d',
            'enddate' => 'required|date_format:Y-m-d|after_or_equal:startdate',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'Time' => 'required|string',
            'Moderator' => 'nullable|string|max:191',
            'Minister' => 'nullable|string|max:191',
            'guest' => 'nullable|string|max:191',
            'location' => 'nullable|string|max:191',
            'Type' => 'required|string|max:191',
            'parishcode' => 'required|string|max:191',
            'parishname' => 'required|string|max:191',
            'eventImg' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $date = strtoupper(substr($request->startdate, 0, -3));
            $dateParts = explode('-', $request->startdate); // Use '-' for date format "YYYY-MM-DD"
            $yr = $dateParts[0];
            $event = event::where('parishcode', 'LIKE', '%' . $request->eventcode . '%')
                ->where('startdate', 'LIKE', '%' . $date . '%')
                ->count();

            if ($event == 0) {
                $event = 1;
                $num_padded = sprintf("%02d", $event);
            } elseif ($event < 10) {
                $event = $event + 1;
                $num_padded = sprintf("%02d", $event);
            } else {
                $num_padded = $event + 1;
            }
            if ($request->hasFile('eventImg')) {

                $fileUploaded = $request->file('eventImg');
                $eventNewPic = $request->parishcode . $yr . $num_padded . '.' . $fileUploaded->getClientOriginalExtension();
                $eventImgPath = $fileUploaded->storeAs('eventImgs', $eventNewPic, 'public');
            } else {
                $eventImgPath = ""; // Or provide a default image path
            }

            $event = event::create([
                'EventId' => $request->parishcode . $num_padded,
                'Title' => $request->Title,
                'Description' => $request->Description,
                'startdate' => $request->startdate,
                'enddate' => $request->enddate,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'Time' => $request->Time,
                'Moderator' => $request->Moderator,
                'Minister' => $request->Minister,
                'guest' => $request->guest,
                'location' => $request->location,
                'Type' => $request->Type,
                'parishcode' => $request->parishcode,
                'parishname' => $request->parishname,
                'eventImg' => $eventImgPath,
            ]);

            if ($event) {
                return response()->json([
                    'status' => 200,
                    'message' => $request->parishcode . $yr . '-' . $num_padded . ' event created sucessfully',
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong ' . $request->parishcode . $yr . '-' . $num_padded . ' event not created',
                ], 200);
            }
        }
    }

    public function updateEvent(Request $request, String $EventId)
    {
        Log::info("Received postData for update: " . json_encode($request->all()));

        $validator = Validator::make($request->all(), [
            'Title' => 'required|string|max:191',
            'Description' => 'nullable|string|max:191',
            'startdate' => 'required|date_format:Y-m-d',
            'enddate' => 'required|date_format:Y-m-d|after_or_equal:startdate',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'Time' => 'required|string|max:191',
            'Moderator' => 'nullable|string|max:191',
            'Minister' => 'nullable|string|max:191',
            'guest' => 'nullable|string|max:191',
            'location' => 'nullable|string|max:191',
            'Type' => 'required|string|max:191',
            'parishcode' => 'required|string|max:191',
            'parishname' => 'required|string|max:191',
            'eventImg' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $event = event::where('EventId', '=', $EventId)->first();

        if (!$event) {
            return response()->json([
                'status' => 500,
                'message' => 'Event not found',
            ], 200);
        }

        if ($request->hasFile('eventImg')) {
            $file = $request->file('eventImg');
            $eventNewPic = $request->parishcode . $EventId . '.' . $file->getClientOriginalExtension();
            $eventImgPath = $file->storeAs('eventImgs', $eventNewPic, 'public');
        } else {
            $eventImgPath = $event->eventImg; // Keep the existing image if no new one is provided
        }

        $event->update([
            'Title' => $request->Title,
            'Description' => $request->Description,
            'startdate' => $request->startdate,
            'enddate' => $request->enddate,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'Time' => $request->Time,
            'Moderator' => $request->Moderator,
            'Minister' => $request->Minister,
            'guest' => $request->guest,
            'location' => $request->location,
            'Type' => $request->Type,
            'parishcode' => $request->parishcode,
            'parishname' => $request->parishname,
            'eventImg' => $eventImgPath,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Event updated successfully',
            'event' => $event,
        ], 200);
    }


    public function FetchAllEvent()
    {
        $allevent = Event::all();
        if ($allevent->count() > 0) {
            $events = $allevent->map(function ($event) {
                $eventImgsPath = $event->eventImg ? Storage::url($event->eventImg) : null;
                $event->eventImgsPublicpath = $eventImgsPath ? URL::to($eventImgsPath) : null;
                Log::info("Received postData: " . json_encode($eventImgsPath));
                return $event;
            });

            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'events' => $events,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No event records found!',
            ], 200);
        }
    }


    public function GetAnEvent($EventId)
    {
        $event = event::where('EventId', '=', $EventId)->first();
        if ($event) {
            return response()->json([
                'status' => 200,
                'message' => $EventId . ' Record fetched successfully',
                'event' => $event,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function DeleteEvent($EventId)
    {

        $event = event::where('EventId', '=', $EventId)->first();
        if ($event) {

            $event->delete();
            return response()->json([
                'status' => 200,
                'message' => ' event deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User/event not found',
            ], 404);
        }
    }

    public function AddNewVineyard(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //validator used in input data(Add New Parish)-copy and paste
            'vineyard' => 'required|string|max:191',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $vineyard = vineyard::where('vineyard', $request->vineyard)->get();

            if (!$vineyard) {
                return response()->json([
                    'status' => 500,
                    'message' => 'vineyard does not exist',
                ], 200);
            } else {

                $vineyard = vineyard::create([
                    'vineyard' => $request->vineyard,

                ]);
            }

            if ($vineyard) {

                return response()->json([
                    'status' => 200,
                    'message' => $request->vineyard . ' post sucessfully created',
                    'vineyard' => $vineyard,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong ' . $request->vineyard . '  not created',
                ], 200);
            }
        }
    }

    public function FetchAllVineyard()
    {
        $allvineyard = vineyard::all();
        if ($allvineyard->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'vineyard ' => $allvineyard,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No Vineyard records found!',
            ], 200);
        }
    }

    public function updateVineyard(Request $request, String $vineyardid)
    {
        $validator = Validator::make($request->all(), [
            //validator used in input data(Add New Event)-copy and paste
            'vineyard' => 'required|string|max:191',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }
        $vineyard = vineyard::where('Id', '=', $vineyardid)->first();

        if ($vineyard) {
            $vineyard->update([
                'vineyard' => $request->vineyard,

            ]);
            return response()->json([
                'status' => 200,
                'message' => ' Vineyard information updated Sucessfully !',
                'vineyard' => $vineyard,
            ], 200);
        } else {

            return response()->json([
                'status' => 500,
                'message' => 'Update failed as Vineyard is not found',
            ], 200);
        }
    }

    public function DeleteVineyard($VineyardId)
    {

        $vineyard = vineyard::where('Id', '=', $VineyardId)->first();
        if ($vineyard) {

            $vineyard->delete();
            return response()->json([
                'status' => 200,
                'message' => 'deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => $vineyard . 'not found',
            ], 404);
        }
    }

    public function AddNewMinistry(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //validator used in input data(Add New Parish)-copy and paste
            'ministry' => 'required|string|max:191',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $ministry = ministry::where('ministry', $request->ministry)->get();

            if (!$ministry) {
                return response()->json([
                    'status' => 500,
                    'message' => 'vineyard does not exist',
                ], 200);
            } else {

                $ministry = ministry::create([
                    'ministry' => $request->ministry,
                ]);
            }

            if ($ministry) {

                return response()->json([
                    'status' => 200,
                    'message' => $request->ministry . ' post sucessfully created',
                    'vineyard' => $ministry,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong ' . $request->ministry . '  not created',
                ], 200);
            }
        }
    }

    public function FetchAllMinistry()
    {
        $allministry = ministry::all();
        if ($allministry->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'ministry' => $allministry,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No ministry records found!',
            ], 200);
        }
    }

    public function updateMinistry(Request $request)
    {
        $data = $request->postData;

        $validator = Validator::make($data, [
            //validator used in input data(Add New Event)-copy and paste
            'ministry' => 'required|string|max:191',

        ]);
        $ministryId = $request->postData['id'];

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }
        $ministry = ministry::where('Id', '=', $ministryId)->first();

        if ($ministry) {
            $ministry->update([
                'ministry' => $request->postData['ministry'],

            ]);
            return response()->json([
                'status' => 200,
                'message' => ' ministry information updated Sucessfully !',
                'ministry' => $ministry,
            ], 200);
        } else {

            return response()->json([
                'status' => 500,
                'message' => 'Update failed as ministry is not found',
            ], 200);
        }
    }

    public function DeleteMinistry($ministryId)
    {

        $ministry = ministry::where('Id', '=', $ministryId)->first();
        if ($ministry) {

            $ministry->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Ministry deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => $ministry . 'not found',
            ], 404);
        }
    }

    public function AddNewVisitor(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //validator used in input data(Add New Parish)-copy and paste
            //'email' => 'required|string|max:191',
            'sname' => 'required|string|max:191',
            'fname' => 'required|string|max:191',
            'gender' => 'required|string|max:191',
            'mobile' => 'required|string|max:191',
            //'whatsapp' => 'required|string|max:191',
            'residence' => 'required|string|max:191',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $visitor = visitors::where('mobile', $request->mobile)->get();

            if ($visitor) {
                return response()->json([
                    'status' => 500,
                    'message' => 'visitor  Info Already exist',
                ], 200);
            } else {

                $visitor = visitors::create([
                    'email' => $request->email,
                    'sname' => $request->sname,
                    'fname' => $request->fname,
                    'Gender' => $request->Gender,
                    'mobile' => $request->mobile,
                    'whatsapp' => $request->whatsapp,
                    'Residence' => $request->Residence,

                ]);
            }

            if ($visitor) {

                return response()->json([
                    'status' => 200,
                    'message' => $request->visitor . ' sucessfully created',
                    'visitor' => $visitor,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong ' . $request->visitor . '  not created',
                ], 200);
            }
        }
    }

    public function FetchAllVisitor()
    {
        $allvisitors = visitors::all();
        if ($allvisitors->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'events ' => $allvisitors,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No event records found!',
            ], 200);
        }
    }

    public function GetAVisitor($Visitor)
    {
        $visitor = visitors::where('$Visitor', '=', $Visitor)->first();
        if ($visitor) {
            return response()->json([
                'status' => 200,
                'message' => $Visitor . ' Record fetched successfully',
                'event' => $visitor,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    //get All countries for user..
    public function fetchCountries()
    {

        // return('All countries here');
        // $urlpath=URL::()

        $countries = Country::all();

        // Modify the response data to include the flag path
        $modifiedCountries = $countries->map(function ($country) {
            $states = countrystate::where('country_id', $country->id)->get();
            return [
                'id' => $country->id,
                'name' => $country->name,
                'short_name' => $country->short_name,
                'flag_img' => URL::to($country->flag_img), // Adjust this based on your actual field name
                // // Include other necessary fields
                'states' => $states,
            ];
        });

        return response()->json([
            'status' => 200,
            'countries' => $modifiedCountries,
        ], 200);
    }

    public function FetchAllStates()
    {

        // Fetch only the required columns: name, short_name, flag_img, and country_code
        $states = countrystate::select('country_id', 'name')->get();

        if ($states->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'states' => $states,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No states records found!',
            ], 200);
        }
    }

    public function AddNewCountry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'short_name' => 'required|string',
            'flag_img' => 'required|string',
            'country_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            $AddCountryCreateResponse = Country::create([
                'name' => $request->name,
                'short_name' => $request->short_name,
                'flag_img' => $request->flag_img,
                'country_code' => $request->country_code,
            ]);
            if ($AddCountryCreateResponse) {
                return response()->json([
                    'status' => 200,
                    'message' => $request->name . ' created successfully',
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => $request->name . ' not successfully as  created ',
                ], 200);
            }
        }
    }

    public function AddNewStates(Request $request)
    {
        // Validate the request input
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|integer',
            'name' => 'required|string',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        // Try to create the new state
        $AddStateCreateResponse = countrystate::create([
            'country_id' => $request->country_id,
            'name' => $request->name,
        ]);

        // Check if the state was created successfully
        if ($AddStateCreateResponse) {
            return response()->json([
                'status' => 200,
                'message' => $request->name . ' created successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => $request->name . ' creation failed',
            ], 500);
        }
    }


    public function AddNewCountries(Request $request)
    {
        // Validate that the input is an array of countries
        $validator = Validator::make($request->all(), [
            'countries' => 'required|array', // Expect an array of countries
            'countries.*.name' => 'required|string',
            'countries.*.short_name' => 'required|string',
            'countries.*.flag_img' => 'required|string',
            'countries.*.country_code' => 'nullable|string',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            // Prepare data for batch insertion
            $countriesData = [];

            foreach ($request->countries as $country) {
                $countriesData[] = [
                    'name' => $country['name'],
                    'short_name' => $country['short_name'],
                    'flag_img' => $country['flag_img'],
                    'country_code' => $country['country_code'],
                    'created_at' => now(), // Set created_at and updated_at timestamps
                    'updated_at' => now(),
                ];
            }

            // Insert data into the 'countries' table in one batch
            $AddCountryCreateResponse = Country::insert($countriesData);

            if ($AddCountryCreateResponse) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Countries created successfully',
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Countries not successfully created',
                ], 500);
            }
        }
    }
    public function AddMultipleStates(Request $request)
    {
        // Validate the request input
        $validator = Validator::make($request->all(), [
            'states' => 'required|array', // Ensure states is an array
            'states.*.country_id' => 'required|integer', // Validate each state's country_id
            'states.*.name' => 'required|string', // Validate each state's name
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        // Iterate over the array of states and create them
        foreach ($request->states as $stateData) {
            countrystate::create([
                'country_id' => $stateData['country_id'],
                'name' => $stateData['name'],
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'States created successfully',
        ], 200);
    }


    public function FetchAllCountry()
    {
        Log::error("Api got here");

        // Fetch only the required columns: name, short_name, flag_img, and country_code
        $countries = Country::select('name', 'short_name', 'flag_img', 'country_code')->get();

        if ($countries->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'countries' => $countries,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No country records found!',
            ], 200);
        }
    }

    public static function sendSmsViaApp($from, $to, $body, $api_token, $append_sender, $direct_refund)
    {
        $url = 'https://www.bulksmsnigeria.com/api/v2/sms';

        $postData = [
            'from' => $from,
            'to' => $to,
            'body' => $body,
            'api_token' => $api_token,
            'append_sender' => $append_sender,
            'gateway' => $direct_refund ? 'direct-refund' : '', // Adjust as needed based on your requirements
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        }

        curl_close($curl);

        return $response;
    }

    public static function decodeParishName($parishInfo)
    {
        $parishData = json_decode($parishInfo->getContent(), true);
        $parishDetails = [];
        if (isset($parishData['allParish'])) {

            foreach ($parishData['allParish'][0] as $key => $value) {
                $parishDetails[$key] = $value; // Assign each key-value pair to the associative array
            }


            return ($parishDetails);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Parish data not found in response',
            ], 404);
        }
    }
    public static function decodeMemberName($memberInfo)
    {
        $memberData = json_decode($memberInfo->getContent(), true);

        $memberDetails = [];
        if (isset($memberData['member'])) { //from api getall member object
            // $sname = array_column($memberData['member'], 'sname');
            // $sname = implode(', ', $sname);
            // $fname = array_column($memberData['member'], 'fname');
            // $fname = implode(', ', $fname);
            // $mname = array_column($memberData['member'], 'mname');
            // $mname = implode(', ', $mname);
            // $Gender = array_column($memberData['member'], 'Gender');
            // $Gender = implode(', ', $Gender);
            // $Title = array_column($memberData['member'], 'Title');
            // $Title = implode(', ', $Title);
            // $fullName = $sname . ' ' . $fname . ' ' . $mname;
            // $memberDetails = ['fullname' => $fullName,
            //     'gender' => $Gender,
            //     'title' => $Title,
            // ];


            foreach ($memberData['member'][0] as $key => $value) {
                $memberDetails[$key] = $value; // Assign each key-value pair to the associative array
            }
            return ($memberDetails);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Member data not found in response',
            ], 404);
        }
    }
    public function FetchAllMembers()
    {
        $allmembers = member::all();
        if ($allmembers->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'members' => $allmembers,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No member records found!',
            ], 200);
        }
    }
    public function getAllPayments()
    {
        $tithePayments = DB::table('tithe')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Tithe' as payment_type"))
            ->get();

        $committeePayments = DB::table('committeememberpayment')
            ->select('id', 'paymentdate as payment_date', 'amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', 'roleName', DB::raw("'Committee' as payment_type"))
            ->get();

        $buildingLevyPayments = DB::table('building_levy')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Building Levy' as payment_type"))
            ->get();

        $offeringPayments = DB::table('offering')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Offering' as payment_type"))
            ->get();

        $baptismPayments = DB::table('baptism_payment')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Baptism' as payment_type"))
            ->get();

        $allPayments = $tithePayments->merge($committeePayments)
            ->merge($buildingLevyPayments)
            ->merge($offeringPayments)
            ->merge($baptismPayments)
            ->merge($tithePayments);

        return response()->json($allPayments);
    }

    public function getMemberPayment($userId)
    {
        // Fetch tithe payments for the given UserId
        $tithePayments = DB::table('tithe')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Tithe' as payment_type"))
            ->where('paidfor', $userId)
            ->get();

        // Fetch committee payments for the given UserId
        $committeePayments = DB::table('committeememberpayment')
            ->select('id', 'paymentdate as payment_date', 'amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', 'roleName', DB::raw("'Committee' as payment_type"))
            ->where('paidfor', $userId)
            ->get();

        // Fetch building levy payments for the given UserId
        $buildingLevyPayments = DB::table('building_levy')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Building Levy' as payment_type"))
            ->where('paidfor', $userId)
            ->get();

        // Fetch offering payments for the given UserId
        $offeringPayments = DB::table('offering')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Offering' as payment_type"))
            ->where('paidfor', $userId)
            ->get();

        // Fetch baptism payments for the given UserId
        $baptismPayments = DB::table('baptism_payment')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Baptism' as payment_type"))
            ->where('paidfor', $userId)
            ->get();

        // Merge all the payments
        $allPayments = $tithePayments->merge($committeePayments)
            ->merge($buildingLevyPayments)
            ->merge($offeringPayments)
            ->merge($tithePayments)
            ->merge($baptismPayments);

        return response()->json($allPayments);
    }

    public function getChurchPayment($pcode)
    {
        // Fetch tithe payments for the given UserId
        $tithePayments = DB::table('tithe')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Tithe' as payment_type"))
            ->where('parishcode', $pcode)
            ->get();

        // Fetch committee payments for the given UserId
        $committeePayments = DB::table('committeememberpayment')
            ->select('id', 'paymentdate as payment_date', 'amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', 'roleName', DB::raw("'Committee' as payment_type"))
            ->where('parishcode', $pcode)
            ->get();

        // Fetch building levy payments for the given UserId
        $buildingLevyPayments = DB::table('building_levy')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Building Levy' as payment_type"))
            ->where('parishcode', $pcode)
            ->get();

        // Fetch offering payments for the given UserId
        $offeringPayments = DB::table('offering')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Offering' as payment_type"))
            ->where('parishcode', $pcode)
            ->get();

        // Fetch baptism payments for the given UserId
        $baptismPayments = DB::table('baptism_payment')
            ->select('id', 'pymtdate as payment_date', 'Amount as amount', 'parishcode', 'parishname', 'receipt', 'paidby', 'paidfor', DB::raw("'Baptism' as payment_type"))
            ->where('parishcode', $pcode)
            ->get();

        // Merge all the payments
        $allPayments = $tithePayments->merge($committeePayments)
            ->merge($buildingLevyPayments)
            ->merge($offeringPayments)
            ->merge($tithePayments)
            ->merge($baptismPayments);

        return response()->json($allPayments);
    }

    public function getSchedule($pcode)
    {
        // Fetch schedules for the specific parish code
        $schedules = schedule::where('parish_code', $pcode)->get();

        // Check if schedules exist for the parish
        if ($schedules->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Schedules fetched successfully',
                'schedules' => $schedules,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No schedules found for this parish',
            ], 404);  // Note: Status code is now 404 for no records
        }
    }

    public function getAnnouncement($pcode)
    {
        // Fetch schedules for the specific parish code
        $announcements = announcement::where('parish_code', $pcode)->get();

        // Check if announcements exist for the parish
        if ($announcements->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'announcements fetched successfully',
                'announcements' => $announcements,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No announcements found for this parish',
            ], 404);  // Note: Status code is now 404 for no records
        }
    }

    public function getDepartment($pcode)
    {
        // Fetch departments for the specific parish code
        $departments = department::where('parish_code', $pcode)->get();

        // Check if departments exist for the parish
        if ($departments->count() > 0) {
            // Map through each department to append the logo's public URL
            $departmentsWithLogos = $departments->map(function ($department) {
                $logoPath = $department->logo ? Storage::url($department->logo) : null;
                $logoPublicPath = $logoPath ? URL::to($logoPath) : null;
                $department->logo = $logoPublicPath; // Add the public path to the department object
                return $department;
            });

            return response()->json([
                'status' => 200,
                'message' => 'Departments fetched successfully',
                'department' => $departmentsWithLogos,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No department found for this parish',
            ], 404);
        }
    }



    public function AddSchedule(Request $request, $pcode)
    {


        $validator = Validator::make($request->all(), [
            'parishcode' => 'required|string|max:191',
            'event' => 'required|string|max:191',
            'person' => 'required|string|max:191',
            'date' => 'required|date|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            $schedule = schedule::create([
                'parish_code' => $request->parishcode,
                'event' => $request->event,
                'person' => $request->person,
                'date' => $request->date,
            ]);

            if ($schedule) {

                return response()->json([
                    'status' => 200,
                    'message' => ' schedule added sucessfully',
                    'schedule' => $schedule,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong schedule not created',
                ], 200);
            }
        }
    }

    public function AddAnnouncement(Request $request, $pcode)
    {


        $validator = Validator::make($request->all(), [
            'parishcode' => 'required|string|max:191',
            'title' => 'required|string|max:191',
            'message' => 'required|string|max:191',
            'date' => 'required|date|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            $announcement = announcement::create([
                'parish_code' => $request->parishcode,
                'title' => $request->title,
                'message' => $request->message,
                'date' => $request->date,
            ]);

            if ($announcement) {

                return response()->json([
                    'status' => 200,
                    'message' => ' announcement added sucessfully',
                    'announcement' => $announcement,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong announcement not created',
                ], 200);
            }
        }
    }

    public function AddDepartment(Request $request, $pcode)
    {
        $validator = Validator::make($request->all(), [
            'parishcode' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'leader' => 'required|string|max:191',
            'team' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            // Handle the logo upload
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $DeptNewPic = $request->parishcode . '.' . $file->getClientOriginalExtension();
                $DeptImgPath = $file->storeAs('DeptImgs', $DeptNewPic, 'public');
            }else {
                $DeptImgPath = ""; // Or provide a default image path
            }
            // Create department entry
            $department = department::create([
                'parish_code' => $request->parishcode,
                'name' => $request->name,
                'logo' => $DeptImgPath, // Save the path of the logo
                'date' => $request->date,
                'leader' => $request->leader,
                'team' => $request->team,
                'status' => $request->status,
            ]);

            if ($department) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Department added successfully',
                    'department' => $department,
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong, department not created',
                ], 500);
            }
        }
    }

}
