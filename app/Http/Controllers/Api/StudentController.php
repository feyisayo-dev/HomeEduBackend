<?php

namespace App\Http\Controllers\Api;

use App\Models\report;
use App\Models\Student;
use App\Models\questions;
use App\Models\leaderboard;
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


    public function leaderboard(Request $request)
    {
        // Validate all incoming data
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'class' => 'required|string',
            'stars' => 'required|integer|min:0',
            'last_practice' => 'required|date_format:Y-m-d',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        try {
            $username = $request->input('username');
            $stars = $request->input('stars');
            $class = $request->input('class');
            $lastPractice = $request->input('last_practice');

            // Check if the user already exists on the leaderboard
            $entry = leaderboard::where('username', $username)->first();

            if ($entry) {
                // Update stars and last practice date
                $entry->stars += $stars;
                $entry->last_practice = $lastPractice;
                $entry->save();
            } else {
                // Create a new leaderboard entry
                leaderboard::create([
                    'username' => $username,
                    'stars' => $stars,
                    'class' => $class,
                    'last_practice' => $lastPractice,
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Leaderboard updated successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Leaderboard update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Failed to update leaderboard',
            ], 500);
        }
    }
    public function ExamQuestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class' => 'required|string|max:191',
            'total_questions' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $class = $request->input('class');
        $JAMB_SUBJECT = $request->input('JAMB_SUBJECT');
        $subject = $request->input('subject');
        $topic = $request->input('topic');
        $total = $request->input('total_questions');

        if ($subject && !$topic) {
            // 🧠 Random questions per topic (at least 1 per topic)
            $topics = questions::where('class', $class)
                ->where('subject', $subject)
                ->pluck('topic')
                ->unique()
                ->values();

            $allQuestions = collect();

            foreach ($topics as $topicName) {
                $topicQuestions = questions::where('class', $class)
                    ->where('subject', $subject)
                    ->where('topic', $topicName)
                    ->inRandomOrder()
                    ->take(1)
                    ->get();

                $allQuestions = $allQuestions->concat($topicQuestions);
            }

            $remaining = $total - $allQuestions->count();

            if ($remaining > 0) {
                $extra = questions::where('class', $class)
                    ->where('subject', $subject)
                    ->whereNotIn('id', $allQuestions->pluck('id'))
                    ->inRandomOrder()
                    ->take($remaining)
                    ->get();

                $allQuestions = $allQuestions->concat($extra);
            }

            return response()->json([
                'status' => 200,
                'data' => $allQuestions->shuffle()->values(),
            ], 200);
        }

        if ($subject && $topic) {
            // 🎯 Random questions under subtopics of a topic
            $questions = questions::where('class', $class)
                ->where('subject', $subject)
                ->where('topic', $topic)
                ->inRandomOrder()
                ->take($total)
                ->get();

            if ($questions->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No questions found for the specified topic.',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'data' => $questions,
            ], 200);
        }

        if ($JAMB_SUBJECT) {
            $subjects = explode(",", $JAMB_SUBJECT);
            $allQuestions = collect();

            foreach ($subjects as $subj) {
                $isEnglish = strtolower(trim($subj)) === 'english';
                $subjectLimit = $isEnglish ? 60 : 40 / (count($subjects) - 1);

                $topics = questions::where('class', $class)
                    ->where('subject', $subj)
                    ->pluck('topic')
                    ->unique()
                    ->values();

                $subjectQuestions = collect();

                foreach ($topics as $topicName) {
                    $topicQuestions = questions::where('class', $class)
                        ->where('subject', $subj)
                        ->where('topic', $topicName)
                        ->inRandomOrder()
                        ->take(1)
                        ->get();

                    $subjectQuestions = $subjectQuestions->concat($topicQuestions);
                }

                $remaining = $subjectLimit - $subjectQuestions->count();

                if ($remaining > 0) {
                    $extra = questions::where('class', $class)
                        ->where('subject', $subj)
                        ->whereNotIn('id', $subjectQuestions->pluck('id'))
                        ->inRandomOrder()
                        ->take($remaining)
                        ->get();

                    $subjectQuestions = $subjectQuestions->concat($extra);
                }

                $allQuestions = $allQuestions->concat($subjectQuestions);
            }

            return response()->json([
                'status' => 200,
                'data' => $allQuestions->shuffle()->values(),
            ], 200);
        }

        return response()->json([
            'status' => 400,
            'message' => 'Invalid parameters provided.',
        ], 400);
    }
}
