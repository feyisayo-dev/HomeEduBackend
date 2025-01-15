<?php

namespace App\Http\Controllers\Api;

use App\Models\report;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\ResponseTrait\original;
use App\Http\Controllers\Api\adminController;

class StudentController extends Controller
{
    use HttpResponses;
    public function login(LoginRequest $request)
    {

        $student = Student::where('email', '=', $request->email)->first();
        if (!$student) {
            return $this->error(
                '',
                message: "Email address not found! Kindly register as a student to login",
                code: 200
            );
        } else {
            if ($student && Hash::check($request['password'], $student->password)) {

                $thumbnailPath = Storage::url($student->thumbnail);
                $thumnailPublicpath = URL::to($thumbnailPath);
                Log::info("Image path: " . json_encode($thumnailPublicpath));

                if ($student['role'] === 'Client') {

                    $response = [
                        'userAbilities' => [
                            [
                                'action' => 'read',
                                'subject' => 'User',
                            ],
                        ],
                        'userData' => [
                            // $student
                            'id' => $student->id,
                            'username' => $student->username,
                            'fullName' => $student->fullName,
                            'dob' => $student->dob,
                            'email' => $student->email,
                            'password' => $student->password,
                            'phoneNumber' => $student->phoneNumber,
                            'class' => $student->class,
                            'parentName' => $student->parentName,
                            'parentContact' => $student->parentContact,
                            'address' => $student->address,
                            'avatar' => $thumnailPublicpath,
                            'role' => $student->role,

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
                        'userData' => [
                            // $student
                            'id' => $student->id,
                            'username' => $student->username,
                            'fullName' => $student->fullName,
                            'dob' => $student->dob,
                            'email' => $student->email,
                            'password' => $student->password,
                            'phoneNumber' => $student->phoneNumber,
                            'class' => $student->class,
                            'parentName' => $student->parentName,
                            'parentContact' => $student->parentContact,
                            'address' => $student->address,
                            'avatar' => $thumnailPublicpath,
                            'role' => $student->role,

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
    public function AddStudent(StoreUserRequest $request)
    {
        // Validation
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generating Student number
        $StudentCount = Student::where('username', 'like', $request->username . '%')->count() + 1;

        // Generate the padded suffix
        $num_padded = sprintf("%03d", $StudentCount);
        $newUsername = $request->username . $num_padded;
        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $fileUploaded = $request->file('thumbnail');
            $StudentNewPic = $request->username . $num_padded . '.' . $fileUploaded->getClientOriginalExtension();
            $thumbnailPath = $fileUploaded->storeAs('thumbnails', $StudentNewPic, 'public');
        } else {
            // Retrieve all thumbnails
            $thumbnails = Storage::disk('public')->files('thumbnails');

            if (!empty($thumbnails)) {
                // Select a random thumbnail
                $thumbnailPath = $thumbnails[array_rand($thumbnails)];

                // Log the randomly chosen thumbnail
                Log::info('Random thumbnail chosen:', ['thumbnail' => $thumbnailPath]);
            } else {
                // Fallback if no thumbnails exist
                $thumbnailPath = ''; // Ensure you have a default image here

                // Log that no thumbnails were available
                Log::warning('No thumbnails available, fallback used.');
            }
        }

        // Create Student
        $Student = Student::create([
            'username' => $newUsername,
            'fullName' => $request->fullName,
            'password' => Hash::make($request->password),
            'dob' => $request->dob,
            'email' => $request->email,
            'phoneNumber' => $request->phoneNumber,
            'class' => $request->class,
            'parentName' => $request->parentName,
            'parentContact' => $request->parentContact,
            'address' => $request->address,
            'thumbnail' => $thumbnailPath,
            'role' => 'Client',
        ]);

        // Send registration SMS and return response
        if ($Student) {

            return response()->json([
                'status' => 200,
                'data' => $Student,
                'message' => 'Student created successfully',
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. User registration was not successful.',
            ], 500);
        }
    }
    public function report($username)
    {
        $report = report::where('username', $username)
            ->leftJoin('subtopics', 'reports.SubtopicId', '=', 'subtopics.SubtopicId')
            ->leftJoin('exams', 'reports.ExamId', '=', 'exams.ExamId')
            ->select(
                'reports.*',
                'subtopics.Subtopic as subtopic_name',
                'exams.Exam as exam_name'
            )
            ->get();

        if ($report->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Report fetched successfully.',
                'data' => $report,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No report found for this user.',
            ], 404);
        }
    }

    public function specificReport(Request $request, $username)
    {
        $validator = Validator::make($request->all(), [
            'subtopicId' => 'nullable|string|max:191',
            'examId' => 'nullable|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        // Ensure either subtopicId or examId is provided
        if (!$request->subtopicId && !$request->examId) {
            return response()->json([
                'status' => 400,
                'message' => 'Provide either subtopicId or examId.',
            ], 400);
        }

        // Fetch report based on subtopicId or examId with joins
        $reportQuery = report::where('reports.username', $username)
            ->leftJoin('subtopics', 'reports.SubtopicId', '=', 'subtopics.SubtopicId')
            ->leftJoin('exams', 'reports.ExamId', '=', 'exams.ExamId')
            ->select(
                'reports.*',
                'subtopics.Subtopic as subtopic_name',
                'exams.ExamId as exam_name'
            );

        if ($request->subtopicId) {
            $reportQuery->where('reports.SubtopicId', $request->subtopicId);
        }

        if ($request->examId) {
            $reportQuery->where('reports.ExamId', $request->examId);
        }

        $report = $reportQuery->get();

        if ($report->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Specific report fetched successfully.',
                'data' => $report,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No report found for the specified subtopic or exam.',
            ], 404);
        }
    }

}
