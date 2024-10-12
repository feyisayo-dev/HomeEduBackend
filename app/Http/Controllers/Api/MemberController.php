<?php

namespace App\Http\Controllers\Api;

use App\Models\tithe;
use App\Models\member;
use App\Models\baptism;
use App\Models\offering;
use App\Models\committee;
use Illuminate\Http\Request;
use App\Models\building_levy;
use App\Traits\HttpResponses;
use App\Models\baptismPayment;
use App\Models\committeemember;
use App\Models\juvelineharvest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\committeememberpayment;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\ResponseTrait\original;
use App\Http\Controllers\Api\adminController;

class MemberController extends Controller
{
    use HttpResponses;

    // public function login(LoginRequest $request){

    //     $member  = member::where('email', '=', $request->email)->first();

    //     if(!$member ){

    //         return $this->error(''," Email address not found! Kindly registered as a member to login ",200);
    //     }else{

    //     if ($member && Hash::check($request['password'], $member->password)) {
    //         return $this->success([
    //             $member ,
    //             'Member Login Sucessfully',
    //             'token'=>$member->createToken('API Token of '.$member ->email)->plainTextToken
    //         ]);

    //     }else{

    //         return response()->json([
    //             'status' => 500,
    //             'message' => 'Something went wrong',
    //         ], 200);
    //     }
    //     }
    // }

    public function login(LoginRequest $request)
    {

        $member = member::where('email', '=', $request->email)->first();
        if (!$member) {
            return $this->error(
                '',
                "Email address not found! Kindly register as a member to login",
                200
            );
        } else {
            if ($member && Hash::check($request['password'], $member->password)) {

                $thumbnailPath = Storage::url($member->thumbnail);
                $thumnailPublicpath = URL::to($thumbnailPath);
                Log::info("Image path: " . json_encode($thumnailPublicpath));

                if ($member['role'] === 'Client') {

                    $response = [
                        'userAbilities' => [
                            [
                                'action' => 'read',
                                'subject' => 'User',
                            ],

                            [
                                'action' => 'read',
                                'subject' => 'Children',
                            ],
                        ],
                        'accessToken' => $member->createToken('API Token of ' . $member->email)->plainTextToken,
                        'userData' => [
                            // $member
                            'id' => $member->id,
                            'sname' => $member->sname,
                            'mname' => $member->mname,
                            'fname' => $member->fname,
                            'Title' => $member->Title,
                            'UserId' => $member->UserId,
                            'dob' => $member->dob,
                            'dot' => $member->dot,
                            'mobile' => $member->mobile,
                            'Altmobile' => $member->Altmobile,
                            'Gender' => $member->Gender,
                            'Status' => $member->Status,
                            'MStatus' => $member->MStatus,
                            'avatar' => $thumnailPublicpath,
                            'email' => $member->email,
                            'ministry' => $member->ministry,
                            'Residence' => $member->Residence,
                            'Country' => $member->Country,
                            'State' => $member->State,
                            'City' => $member->City,
                            'role' => $member->role,
                            'parishcode' => $member->parishcode,
                            'parishname' => $member->parishname,
                            // ... add other user data as needed
                        ],
                    ];
                } else {
                    $response = [
                        'userAbilities' => [
                            [
                                'action' => 'manage',
                                'subject' => 'all',
                            ],
                            // ... add other abilities as needed
                        ],
                        'accessToken' => $member->createToken('API Token of ' . $member->email)->plainTextToken,
                        'userData' => [
                            // $member
                            'id' => $member->id,
                            'sname' => $member->sname,
                            'fname' => $member->fname,
                            'mname' => $member->mname,
                            'Title' => $member->Title,
                            'UserId' => $member->UserId,
                            'Altmobile' => $member->Altmobile,
                            'dot' => $member->dot,
                            'dob' => $member->dob,
                            'mobile' => $member->mobile,
                            'Gender' => $member->Gender,
                            'Status' => $member->Status,
                            'MStatus' => $member->MStatus,
                            'avatar' => $thumnailPublicpath,
                            'email' => $member->email,
                            'ministry' => $member->ministry,
                            'Residence' => $member->Residence,
                            'Country' => $member->Country,
                            'State' => $member->State,
                            'City' => $member->City,
                            'role' => $member->role,
                            'parishcode' => $member->parishcode,
                            'parishname' => $member->parishname,
                            // ... add other user data as needed
                        ],
                    ];
                }
                return response()->json($response);
            } else {
                $response = [
                    'status' => 'false', // or 'error' based on your preference
                    'message' => 'Invalid credentials',
                ];

                return response()->json(
                    $response,
                    401
                );
            }
        }
    }

    public function Addmember(StoreUserRequest $request)
    {
        $fetchparish = adminController::FetchAllParishes($request->parishcode);
        $decode_Parish = adminController::decodeParishName($fetchparish);
        $parishName = $decode_Parish['parishname'];
        $append_sender = $from = $parishName;
        $body = 'Welcome to ' . $parishName . ', We are happy to see you worship the Lord with us, God will meet you at your point of need. Amen';
        $api_token = 'Bw3uqEgd4AF7c6A7Gv5BGWJTTfCP5V9psAs1xM8rGPd59eWSNqYy0QSJSJZ9';
        $direct_refund = 'direct-hosted';
        $to = $request->mobile;

        // Validation
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generating member number
        $ParismemberCount = member::where('parishcode', $request->parishcode)->count() + 1;
        $num_padded = sprintf("%02d", $ParismemberCount);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $fileUploaded = $request->file('thumbnail');
            $memberNewPic = $request->parishcode . $num_padded . '.' . $fileUploaded->getClientOriginalExtension();
            $thumbnailPath = $fileUploaded->storeAs('thumbnails', $memberNewPic, 'public');
        } else {
            $thumbnailPath = ""; // Or provide a default image path
        }

        // Create member
        $member = member::create([
            'UserId' => $request->parishcode . $num_padded,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sname' => $request->sname,
            'fname' => $request->fname,
            'mname' => $request->mname,
            'Gender' => $request->Gender,
            'dob' => $request->dob,
            'mobile' => $request->mobile,
            'Altmobile' => $request->Altmobile,
            'Residence' => $request->address,
            'Country' => $request->Country,
            'State' => $request->State,
            'City' => $request->City,
            'Title' => $request->Title,
            'dot' => $request->dot,
            'MStatus' => $request->MStatus,
            'ministry' => $request->ministry,
            'Status' => $request->Status,
            'thumbnail' => $thumbnailPath,
            'parishcode' => $request->parishcode,
            'parishname' => $parishName,
            'role' => 'Client',
        ]);

        // Send registration SMS and return response
        if ($member) {
            $sendRegistrationSms = adminController::sendSmsViaApp($from, $to, $body, $api_token, $append_sender, $direct_refund);

            return response()->json([
                'status' => 200,
                'data' => $member,
                'sms' => $sendRegistrationSms,
                'message' => 'Member created successfully',
                // Uncomment if needed: 'token' => $member->createToken('API Token of '.$member->email)->plainTextToken
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. User registration was not successful.',
            ], 500);
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
                'message' => 'No member records found!',
            ], 200);
        }
    }

    public function Fetchmemberbyparish($pcode)
    {

        $members = member::where('parishcode', '=', $pcode)->get();

        if ($members->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Records fetched successfully',
                'records' => $members,  // Return as an array of records
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No records found for the given pcode',
            ], 404);
        }
    }

    public function fetchAllBaptismRecords()
    {
        $allrecords = baptism::all();
        if ($allrecords->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'members' => $allrecords,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No member records found!',
            ], 200);
        }
    }

    public function fetchBaptismRecord(Request $request)
    {
        $UserId = $request->postData['UserId'];

        // Fetch multiple records based on the UserId
        $baptisms = baptism::where('UserId', '=', $UserId)->get();

        if ($baptisms->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Records fetched successfully',
                'records' => $baptisms,  // Return as an array of records
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No records found for the given UserId',
            ], 404);
        }
    }


    public function addBaptismRecord(Request $request)
    {
        $UserId = $request->baptismData['UserId'];
        $Status = $request->baptismData['Status'];
        $Amount = $request->baptismData['Amount'];
        $Year_of_Joining = $request->baptismData['Year_of_Joining'];
        $data = $request->baptismData;

        Log::info("<----userId-->" . json_encode($data));

        $validator = Validator::make($data, [
            'UserId' => 'required|string|max:191',
            'Status' => 'required|boolean|max:191',
            'Amount' => 'required|string|max:191',
            'Year_of_Joining' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            $baptism = baptism::create([
                'UserId' => $UserId,
                'Status' => $Status,
                'Amount' => $Amount,
                'Year_of_Joining' => $Year_of_Joining,
            ]);

            if ($baptism) {

                return response()->json([
                    'status' => 200,
                    'message' => ' baptism paid sucessfully',
                    'baptism' => $baptism,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong baptism not created',
                ], 200);
            }
        }
    }

    public function ChangePassword(Request $request, $UserId)
    {
        // Step 1: Validate the request
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed', // Ensure you include confirmation field in the request
        ]);

        // Step 2: Find the user
        $member = Member::where('UserId', '=', $UserId)->first(); // Use first() to get a single instance

        // Check if user exists
        if (!$member) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404); // Status code 404
        }

        // Step 3: Check if current password is correct
        if (!Hash::check($request->current_password, $member->password)) {
            return response()->json([
                'status' => 403,
                'message' => 'Current password is incorrect',
            ], 403); // Status code 403
        }

        // Step 4: Hash the new password and update the user's password
        $member->password = Hash::make($request->new_password);
        $member->save();

        // Step 5: Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Password changed successfully',
        ], 200); // Status code 200
    }

    // public function fetchAllMembers()
    // {

    //     $members = member::with('children')->get();
    //     $memberCount = $members->count();
    //     $page = 1;
    //     $pageSize = 10;
    //     $totalPages = ceil($memberCount / $pageSize);

    //     if ($members->count() > 0) {

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Record fetched successfully',
    //             'usersDetails' => $members->toArray(),
    //             'users' => array_map(function ($member) {
    //                 if (empty($member['thumbnail'])) {
    //                     $thumnailPublicpath = ' ';
    //                 } else {

    //                     $thumbnailPath = Storage::url($member['thumbnail']);
    //                     $thumnailPublicpath = URL::to($thumbnailPath);
    //                 }
    //                 return [
    //                     'id' => $member['id'],
    //                     'fullName' => $member['sname'] . ' ' . $member['fname'], // Adjust the attribute names accordingly
    //                     'gender' => $member['Gender'],
    //                     'avatar' => $thumnailPublicpath,
    //                     'email' => $member['email'],
    //                     'mobile' => $member['mobile'],
    //                     'role' => $member['role'],
    //                     // Add other user data as needed
    //                 ];
    //             }, $members->toArray()),

    //             // 'users' => $members,
    //             // 'usersDetails' => array_map(function ($member) {
    //             //     return [
    //             //         'id' => $member->id,
    //             //         'fullName' => $member->fullName, // Adjust the attribute names accordingly
    //             //         'username' => $member->username,
    //             //         'avatar' => $member->avatarPublicPath,
    //             //         'email' => $member->email,
    //             //         'role' => $member->role,
    //             //         // Add other user data as needed
    //             //     ];
    //             // }, $members),
    //             // 'users' => [
    //             //     // $member
    //             //     'id' => $members->id,
    //             //     // 'fullName' => $fullName, // Adjust the attribute names accordingly
    //             //     // 'username' => $members->username,
    //             //     'avatar' =>$thumnailPublicpath,
    //             //     'email' => $members->email,
    //             //     'role' => $members->role,
    //             //     // ... add other user data as needed
    //             // ],
    //             'totalUsers' => $memberCount,
    //             'totalPages' => $totalPages,
    //             'page' => $page,

    //         ], 200);
    //     } else {
    //         return response()->json([
    //             'status' => 404,
    //             'message' => 'No records found!',
    //             'member_count' => 0,
    //             'total_pages' => 0,
    //         ], 404);
    //     }

    // }

    public static function GetMember($UserId)
    {
        $member = Member::where('UserId', '=', $UserId)->first();

        if ($member) { // Check if the member exists
            // Ensure the thumbnail path is only set if it exists
            $thumbnailPath = $member->thumbnail ? Storage::url($member->thumbnail) : null;
            $thumbnailPublicPath = $thumbnailPath ? URL::to($thumbnailPath) : null;

            // Add the public path to the member object
            $member->thumbnail = $thumbnailPublicPath;

            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'member' => $member, // Return the modified member object with the public path
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }



    public function updateMember(Request $request, String $UserId)

    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:191',
            'sname' => 'required|string|max:191',
            'fname' => 'required|string|max:191',
            'mname' => 'nullable|string|max:191',
            'Gender' => 'required|string|max:191',
            'dob' => 'required|string|max:191',
            'mobile' => 'required|string|max:191',
            'Altmobile' => 'nullable|string|max:191',
            'Residence' => 'required|string|max:191',
            'Country' => 'required|string|max:191',
            'State' => 'required|string|max:191',
            'City' => 'required|string|max:191',
            'Title' => 'nullable|string|max:191',
            'dot' => 'nullable|string|max:191',
            'MStatus' => 'required|string|max:191',
            'ministry' => 'required|string|max:191',
            'Status' => 'nullable|string|max:191',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            $ParismemberCount = member::where('parishcode', $request->parishcode)->count() + 1;
            $num_padded = sprintf("%02d", $ParismemberCount);

            if ($request->hasFile('thumbnail')) {
                $fileUploaded = $request->file('thumbnail');
                $memberNewPic = $request->parishcode . $num_padded . '.' . $fileUploaded->getClientOriginalExtension();
                $thumbnailPath = $fileUploaded->storeAs('thumbnails', $memberNewPic, 'public');
            } else {
                $thumbnailPath = "";
            }


            $member = Member::where('UserId', '=', $UserId)->first();
            Log::info("Received Status: " . json_encode($request->Status));

            if ($member) {
                $member->update([
                    'email' => $request->email,
                    'sname' => $request->sname,
                    'fname' => $request->fname,
                    'mname' => $request->mname,
                    'Gender' => $request->Gender,
                    'dob' => $request->dob,
                    'mobile' => $request->mobile,
                    'Altmobile' => $request->Altmobile,
                    'Residence' => $request->Residence,
                    'Country' => $request->Country,
                    'State' => $request->State,
                    'City' => $request->City,
                    'Title' => $request->Title,
                    'dot' => $request->dot,
                    'MStatus' => $request->MStatus,
                    'ministry' => $request->ministry,
                    'Status' => $request->Status,
                    'thumbnail' => $thumbnailPath,
                    'parishcode' => $request->parishcode,
                    'parishname' => $request->parishname,
                ]);
                Log::info("Member updated with Status: " . $member->Status);

                return response()->json([
                    'status' => 200,
                    'message' => 'Member information updated successfully!',
                    'member' => $member,
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed as user is not found',
                ], 500);
            }
        }
    }

    public function deleteMember($UserId)
    {

        $member = member::where('UserId', '=', $UserId)->first();
        if ($member) {

            $member->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Member deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User/Member not found',
            ], 404);
        }
    }

    public function AddNewTithe(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'paidby' => 'required|string|max:191',
            'amount' => 'required|string|max:191',
            'paymentdate' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {



            $tithe = tithe::create([
                'pymtdate' => $request->paymentdate,
                'Amount' => $request->amount,
                'parishcode' => $request->parishcode,
                'parishname' => $request->parishname,
                'receipt' => $request->receipt,
                'paidby' => $request->paidby,
                'paidfor' => $request->paidfor,
            ]);

            if ($tithe) {

                return response()->json([
                    'status' => 200,
                    'message' => ' Tithe paid sucessfully',
                    'tithe' => $tithe,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong ' . ' tithe not created',
                ], 200);
            }
        }
    }

    public function AddNewOffering(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'paidby' => 'required|string|max:191',
            'amount' => 'required|string|max:191',
            'paymentdate' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {



            $offering = offering::create([
                'pymtdate' => $request->paymentdate,
                'Amount' => $request->amount,
                'parishcode' => $request->parishcode,
                'parishname' => $request->parishname,
                'receipt' => $request->receipt,
                'paidby' => $request->paidby,
                'paidfor' => $request->paidfor,
            ]);

            if ($offering) {

                return response()->json([
                    'status' => 200,
                    'message' => ' Offering paid sucessfully',
                    'offering' => $offering,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong ' . ' offering not created',
                ], 200);
            }
        }
    }

    public function AddNewbuildingLevy(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'paidby' => 'required|string|max:191',
            'amount' => 'required|string|max:191',
            'paymentdate' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {



            $building_levy = building_levy::create([
                'pymtdate' => $request->paymentdate,
                'Amount' => $request->amount,
                'parishcode' => $request->parishcode,
                'parishname' => $request->parishname,
                'receipt' => $request->receipt,
                'paidby' => $request->paidby,
                'paidfor' => $request->paidfor,
            ]);

            if ($building_levy) {

                return response()->json([
                    'status' => 200,
                    'message' => 'Building Levy paid sucessfully',
                    'building_levy' => $building_levy,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong ' . ' building_levy not created',
                ], 200);
            }
        }
    }
    public function AddNewBaptismPayment(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'paidby' => 'required|string|max:191',
            'amount' => 'required|string|max:191',
            'paymentdate' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {



            $baptismPayment = baptismPayment::create([
                'pymtdate' => $request->paymentdate,
                'Amount' => $request->amount,
                'parishcode' => $request->parishcode,
                'parishname' => $request->parishname,
                'receipt' => $request->receipt,
                'paidby' => $request->paidby,
                'paidfor' => $request->paidfor,
            ]);

            if ($baptismPayment) {

                return response()->json([
                    'status' => 200,
                    'message' => ' Baptism paid sucessfully',
                    'baptismPayment' => $baptismPayment,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong ' . ' baptismPayment not created',
                ], 200);
            }
        }
    }

    public function GetATithe($UserId)
    {
        $tithe = tithe::where('UserId', '=', $UserId)->first();
        if ($tithe) {
            return response()->json([
                'status' => 200,
                'message' => $UserId . ' Record fetched successfully',
                'tithe ' => $tithe,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function GetAllParishTithe($parishcode)
    {
        $tithe = tithe::where('parishcode', '=', $parishcode)->get();
        if ($tithe) {
            return response()->json([
                'status' => 200,
                'message' => $parishcode . ' Record fetched successfully',
                'tithe ' => $tithe,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function UpdateTithe(Request $request, String $UserId)
    {

        $validator = Validator::make($request->all(), [
            //validator used in input data(tithe)-copy and paste
            //'UserId'       => 'required|string|max:191',
            'pymtdate' => 'required|string|max:191',
            'Amount' => 'required|string|max:191',
            'pymtImg' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            //'sname' => 'required|string|max:191',
            //'fname' => 'required|string|max:191',
            //'mname' => 'required|string|max:191',
            //'parishcode'  =>'required|string|max:191',
            //'parishname' =>'required|string|max:191',

            // we dont have to add userId bc we will generate it ourselve
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            if ($request->hasFile('pymtImg')) {
                $file = $request->file('pymtImg');
                $pymtImg = $request->pymtdate . '' . $UserId . '.' . $file->getClientOriginalExtension();
                $pymtImgPath = $file->storeAs('pymtImgs', $pymtImg, 'public');
            } else {
                $pymtImgPath = null; // Or provide a default image path
            }

            $fetchparish = adminController::FetchAllParishes($request->parishcode)->original['Allparish'];
            $parishNames = implode(', ', array_column($fetchparish, 'parishname'));

            $tithe = validator($request->all());

            $tithe = tithe::where('UserId', '=', $UserId)->first();

            if ($tithe) {
                $tithe->update([
                    //'UserId'       => $request->UserId,
                    //'sname'  =>$request->sname,
                    //'fname'  =>$request->fname,
                    //'mname'  =>$request->mname,
                    'pymtdate' => $request->pymtdate,
                    'Amount' => $request->Amount,
                    //'parishcode'     =>$request->pariscode,
                    //'parishname'        =>$request->parisname,
                    'pymtImg' => $pymtImgPath,
                ]);
                return response()->json([
                    'status' => 200,
                    'message' => 'Tithe information updated Sucessfully !',
                    'tithe' => $tithe,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed as user is not found',
                ], 200);
            }
        }
    }

    public function DeleteTithe($UserId)
    {

        $tithe = tithe::where('UserId', '=', $UserId)->first();
        if ($tithe) {

            $tithe->delete();
            return response()->json([
                'status' => 200,
                'message' => 'tithe deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User/tithe not found',
            ], 404);
        }
    }

    public function AddNewJuvelineHarvest(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //validator used in input data(Add New Parish)-copy and paste
            'UserId' => 'required|string|max:191',
            'pymtdate' => 'required|string|max:191',
            'Amount' => 'required|string|max:191',
            'pymtImg' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            // we dont have to add picode bc we will generate it ourselve
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $member = member::where('UserId', $request->UserId)->get();

            if ($request->hasFile('pymtImg')) {

                $fileUploaded = $request->file('pymtImg');
                $jhpaymentImg = $request->pymtdate . '' . $request->UserId . '.' . $fileUploaded->getClientOriginalExtension();
                $pymtImgPath = $fileUploaded->storeAs('jharvestpymtImgs', $jhpaymentImg, 'public');
            } else {
                $pymtImgPath = ""; // Or provide a default image path
            }
            if (!$member) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Member does not exist',
                ], 200);
            } else {

                $Surname = $member[0]['sname'];
                $FirstName = $member[0]['fname'];
                $MiddleName = $member[0]['mname'];
                $pariscode = $member[0]['parishcode'];
                $parisname = $member[0]['parishname'];

                $juvelineharvest = juvelineharvest::create([
                    'UserId' => $request->UserId,
                    'FullName' => $Surname . ' ' . $FirstName . ' ' . $MiddleName,
                    'pymtdate' => $request->pymtdate,
                    'Amount' => $request->Amount,
                    'parishcode' => $pariscode,
                    'parishname' => $parisname,
                    'pymtImg' => $pymtImgPath,
                ]);
            }

            if ($juvelineharvest) {

                return response()->json([
                    'status' => 200,
                    'message' => ' juvelineharvest paid sucessfully',
                    'juvelineharvest' => $juvelineharvest,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong ' . ' juvelineharvest not created',
                ], 200);
            }
        }
    }

    public function GetAllParishJuvelineDue($parishcode)
    {
        $juvelineharvest = juvelineharvest::where('parishcode', '=', $parishcode)->get();
        if ($juvelineharvest) {
            return response()->json([
                'status' => 200,
                'message' => $parishcode . ' Record fetched successfully',
                'juvelineharvest ' => $juvelineharvest,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function GetAJuvelineDue($UserId)
    {
        $juvelineharvest = juvelineharvest::where('UserId', '=', $UserId)->first();
        if ($juvelineharvest) {
            return response()->json([
                'status' => 200,
                'message' => $UserId . ' Record fetched successfully',
                'juvelineharvest ' => $juvelineharvest,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function UpdateJuvelineDue(Request $request, String $UserId)
    {

        $validator = Validator::make($request->all(), [
            //validator used in input data(tithe)-copy and paste
            //'UserId'       => 'required|string|max:191',
            'pymtdate' => 'required|string|max:191',
            'Amount' => 'required|string|max:191',
            'pymtImg' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            //'sname' => 'required|string|max:191',
            //'fname' => 'required|string|max:191',
            //'mname' => 'required|string|max:191',
            //'parishcode'  =>'required|string|max:191',
            //'parishname' =>'required|string|max:191',

            // we dont have to add userId bc we will generate it ourselve
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            if ($request->hasFile('pymtImg')) {
                $file = $request->file('pymtImg');
                $jhpaymentImg = $request->pymtdate . '' . $UserId . '.' . $file->getClientOriginalExtension();
                $jhpaymentImgPath = $file->storeAs('jhpaymentImgs', $jhpaymentImg, 'public');
            } else {
                $jhpaymentImgPath = null; // Or provide a default image path
            }

            $fetchparish = adminController::FetchAllParishes($request->parishcode)->original['Allparish'];
            $parishNames = implode(', ', array_column($fetchparish, 'parishname'));

            $juvelineharvest = validator($request->all());

            $juvelineharvest = juvelineharvest::where('UserId', '=', $UserId)->first();

            if ($juvelineharvest) {
                $juvelineharvest->update([
                    //'UserId'       => $request->UserId,
                    //'sname'  =>$request->sname,
                    //'fname'  =>$request->fname,
                    //'mname'  =>$request->mname,
                    'pymtdate' => $request->pymtdate,
                    'Amount' => $request->Amount,
                    //'parishcode'     =>$request->pariscode,
                    //'parishname'        =>$request->parisname,
                    'pymtImg' => $jhpaymentImgPath,
                ]);
                return response()->json([
                    'status' => 200,
                    'message' => 'juvelineharvest information updated Sucessfully !',
                    'juvelineharvest' => $juvelineharvest,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Update failed as user is not found',
                ], 200);
            }
        }
    }

    public function DeleteJuvelineDue($UserId)
    {

        $juvelineharvest = juvelineharvest::where('UserId', '=', $UserId)->first();
        if ($juvelineharvest) {

            $juvelineharvest->delete();
            return response()->json([
                'status' => 200,
                'message' => 'juvelineharvest deleted  successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User/juvelineharvest not found',
            ], 404);
        }
    }

    //     public function searchAllMembers(Request $request)
    // {
    //     $searchQuery = $request->input('q');

    //     $query = Member::with('children');

    //     // Apply search filter if a search query is provided
    //     if ($searchQuery) {
    //         $query->where(function ($subquery) use ($searchQuery) {
    //             $subquery->where('sname', 'like', '%' . $searchQuery . '%')
    //                      ->orWhere('fname', 'like', '%' . $searchQuery . '%')
    //                      ->orWhere('email', 'like', '%' . $searchQuery . '%');
    //             // Add additional columns as needed for searching
    //         });
    //     }

    //     $members = $query->get();
    //     $memberCount = $members->count();
    //     $page = 1;
    //     $pageSize = 10;
    //     $totalPages = ceil($memberCount / $pageSize);

    //     if ($members->count() > 0) {
    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Record fetched successfully',
    //             'usersDetails' => $members->toArray(),
    //             'users' => array_map(function ($member) {
    //                 // ... (your existing transformation logic)
    //             }, $members->toArray()),
    //             'totalUsers' => $memberCount,
    //             'totalPages' => $totalPages,
    //             'page' => $page,
    //         ], 200);
    //     } else {
    //         return response()->json([
    //             'status' => 404,
    //             'message' => 'No records found!',
    //             'member_count' => 0,
    //             'total_pages' => 0,
    //         ], 404);
    //     }
    // }

    // public function logout()
    // {
    //     Auth::user()->currentAccessToken()->delete();

    //     return $this->success([
    //         'message' => 'You have succesfully been logged out and your token has been removed'
    //     ]);
    // }

    public function addCommitteePayment(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'committeRefno' => 'required|string|max:191',
            'paidby' => 'required|string|max:191',
            'amount' => 'required|string|max:191',
            'paymentdate' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {

            $createCommitteePayment = committeememberpayment::create([
                'committeRefno' => $request->committeRefno,
                'committename' => $request->committeName,
                'paidfor' => $request->paidfor,
                'paidby' => $request->paidby,
                'parishcode' => $request->parishcode,
                'parishname' => $request->parishname,
                'amount' => $request->amount,
                'receipt' => $request->receipt,
                'roleName' => $request->roleName,
                'paymentdate' => $request->paymentdate,
            ]);

            if ($createCommitteePayment) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Payment Uploaded successfully',
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Payment not uploaded successfully',
                ], 500);
            }
        }
    }

    public function GetACommitteeMemberPayment($userId)
    {

        $payments = committeememberpayment::where('UserId', $userId)->get();

        if ($payments) {
            return response()->json([
                'status' => 200,
                'message' => ' Record fetched successfully',
                'data' => $payments,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function GetMemberPymtforACommitee($UserId, $committeRefno)
    {

        $payments = committeememberpayment::where('UserId', $UserId)->where('committeRefno', $committeRefno)->get();

        if (($payments)) {
            return response()->json([
                'status' => 200,
                'message' => ' Record fetched successfully',
                'data' => $payments,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }
    public function GetACommitteeNamePayment($committeRefno)
    {
        $allData = committeememberpayment::where('committeRefno', '=', 'NIG0120240422182901')->get();
        //$allData = committeepayment::where('committeRefno', '=', $committeRefno)->get();
        return $allData;
        if ($allData) {
            return response()->json([
                'status' => 200,
                'message' => ' Record fetched successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function changeCommitteePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //validator used in input data-copy and paste
            'oldcommitteRefno' => 'required|string|max:191',
            'oldmemberId' => 'required|string|max:191',
            'committeRefno' => 'required|string|max:191',
            'memberId' => 'required|string|max:191',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $getNewMemberDetails = MemberController::GetMember($request->new_member_id); //GetMember function is from Get member controller

        $decodeMemberName = adminController::decodeMemberName($getNewMemberDetails); // decodeMemberName function is down down

        $fullName = $decodeMemberName['sname'] . ' ' . $decodeMemberName['fname'] . ' ' . $decodeMemberName['mname'];
        $title = $decodeMemberName['Title'];
        $gender = $decodeMemberName['Gender'];
        $committename = $decodeMemberName['committename'];

        $getOldcommitteeMemberPayment = committeememberpayment::where('UserId', $request->memberId)->where('committeRefno', $request->committeRefno)->first();

        if ($getOldcommitteeMemberPayment && $getNewMemberDetails) {
            // Update the old committee member details with the new member details
            $getOldcommitteeMemberPayment->update([
                'paidfor' => $fullName,
                'UserId' => $request->new_member_id,
                "committename" => $committename,
                // Add other fields that you want to update
            ]);
        }
        if ($getOldcommitteeMemberPayment) {
            return response()->json([
                'status' => 200,
                'message' => 'Message Change will be change based on Parochail Chairman Approval',
                'data' => $getOldcommitteeMemberPayment,
            ], 200);
        }
    }
}
