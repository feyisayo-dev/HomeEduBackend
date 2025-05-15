<?php

namespace App\Http\Controllers\Api;

use App\Models\report;
use App\Models\topics;
use App\Models\Classes;
use App\Models\Student;
use App\Models\examples;
use App\Models\subjects;
use App\Models\subtopic;
use App\Models\narration;
use App\Models\questions;
use App\Models\leaderboard;
use Illuminate\Support\Str;
use App\Models\explanations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use WisdomDiala\Countrypkg\Models\Country;
use Illuminate\Http\ResponseTrait\original;
use App\Http\Controllers\Api\StudentController;
use WisdomDiala\Countrypkg\Models\State as countrystate;
use Illuminate\Support\Facades\Hash;

class adminController extends Controller
{

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
    public function AddClasses(Request $request)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'ClassName' => 'required|string',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        // Create the class record
        $Classes = Classes::create([
            'ClassName' => $request->ClassName,
        ]);

        // Check if creation was successful and return a response
        if ($Classes) {
            return response()->json([
                'status' => 200,
                'message' => 'Class created successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Class creation failed',
            ], 500);
        }
    }

    public function fetchAllClasses()
    {
        $allrecords = Classes::all();
        if ($allrecords->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'Classes' => $allrecords,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No Classes records found!',
            ], 200);
        }
    }

    public function AddQuestions(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:191',
            'content' => 'required|string',
            'options' => 'nullable|string',
            'answer' => 'required|string',
            'user_class' => 'required|string',
            'images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480'

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            // Generate the padded suffix
            $QuestionId = $request->subtopic . rand(0, 999);
            if ($request->hasFile('images')) {
                $fileUploaded = $request->file('images');
                $newQuestionImage = $request->subtopic . rand(0, 999) . '.' . $fileUploaded->getClientOriginalExtension();
                $thumbnailPath = $fileUploaded->storeAs('QuestionImages', $newQuestionImage, 'public');
                $thumbnailNewPath =  url('storage/' . $thumbnailPath); // Generate the full URL
            } else {
                // Fallback if no thumbnails exist
                $thumbnailNewPath = ''; // Ensure you have a default image here
            }


            $questions = questions::create([
                'QuestionId' => $QuestionId,
                'type' => $request->type,
                'content' => $request->content,
                'options' => $request->options,
                'answer' => $request->answer,
                'class' => $request->user_class,
                'subtopic' => $request->subtopic,
                'image' => $thumbnailNewPath,
            ]);

            if ($questions) {

                return response()->json([
                    'status' => 200,
                    'message' => ' questions added sucessfully',
                    'questions' => $questions,
                ], 200);
            } else {

                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong questions not created',
                ], 200);
            }
        }
    }


    public function AddSubjects(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Class' => 'required|string',
            'Subject' => 'required|string',
            'Icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            // Calculate the SubjectCount
            $SubjectCount = subjects::where('Class', 'like', $request->Class . '%')->count() + 1;

            // Generate the padded suffix
            $num_padded = sprintf("%03d", $SubjectCount);

            // Concatenate strings properly using the '.' operator
            $SubjectId = rand(1, 999) . $num_padded;

            if ($request->hasFile('Icon')) {
                $fileUploaded = $request->file('Icon');
                // Generate a unique file name with ExamplesCountId
                $newQuestionImage = $request->Subject . '_icon.' . $fileUploaded->getClientOriginalExtension();
                $relativePath = $fileUploaded->storeAs('SubjectIcon', $newQuestionImage, 'public');
                $SubjectIcon =  url('storage/' . $relativePath); // Generate the full URL
            } else {
                $SubjectIcon = null;
            }

            // Create the topic
            $subjects = subjects::create([
                'SubjectId' => $SubjectId,
                'Class' => $request->Class,
                'Subject' => $request->Subject,
                'Icon' => $SubjectIcon,
            ]);

            if ($subjects) {
                return response()->json([
                    'status' => 200,
                    'message' => 'subjects added successfully',
                    'subjects' => $subjects,
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong. subjects not created',
                ], 500); // Fixed status code
            }
        }
    }
    public function AddTopic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Topic' => 'required|string',
            'Subject' => 'required|string',
            'Class' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            // Calculate the TopicCount
            $TopicCount = topics::where('TopicId', 'like', $request->Topic . '%')->count() + 1;

            // Generate the padded suffix
            $num_padded = sprintf("%03d", $TopicCount);

            // Concatenate strings properly using the '.' operator
            $TopicId = $request->Topic . $num_padded;

            // Create the topic
            $Topics = topics::create([
                'TopicId' => $TopicId,
                'Topic' => $request->Topic,
                'Class' => $request->Class,
                'Subject' => $request->Subject,
            ]);

            if ($Topics) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Topics added successfully',
                    'Topics' => $Topics,
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong. Topics not created',
                ], 500); // Fixed status code
            }
        }
    }

    public function AddSubTopic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'TopicId' => 'required|string|max:191',
            'Subtopic' => 'required|string',
            'Class' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            // Check if TopicId exists in the topics table
            $topicExists = topics::where('TopicId', $request->TopicId)->exists();

            if (!$topicExists) {
                return response()->json([
                    'status' => 404,
                    'message' => 'TopicId does not exist in the topics table',
                ], 404);
            }

            // Calculate the SubTopicCount
            $SubTopicCount = subtopic::where('TopicId', 'like', $request->TopicId . '%')->count() + 1;

            // Generate the padded suffix
            $num_padded = sprintf("%03d", $SubTopicCount);
            $SubTopicCountId = $request->TopicId . $num_padded;

            // Create the subtopic
            $SubTopics = subtopic::create([
                'TopicId' => $request->TopicId,
                'SubtopicId' => $SubTopicCountId,
                'Subtopic' => $request->Subtopic,
            ]);

            if ($SubTopics) {
                return response()->json([
                    'status' => 200,
                    'message' => 'SubTopics added successfully',
                    'SubTopics' => $SubTopics,
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong. SubTopics not created',
                ], 500); // Fixed status code
            }
        }
    }
    public function AddExplanation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'SubtopicId' => 'required|string|max:191',
            'Content' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        // Check if subtopic exists in the database
        $subTopicExists = subtopic::where('SubtopicId', $request->SubtopicId)->exists();

        if (!$subTopicExists) {
            return response()->json([
                'status' => 404,
                'message' => 'SubtopicId does not exist in the topics table',
            ], 404);
        }

        // Generate Explanation ID
        $ExplanationCount = explanations::where('ExplanationId', 'like', $request->SubtopicId . '%')->count() + 1;
        $num_padded = sprintf("%03d", $ExplanationCount);
        $ExplanationCountId = $request->SubtopicId . 'expn' . $num_padded;

        $content = json_decode($request->Content, true);
        $processedContent = [];

        foreach ($content as $item) {
            if ($item['type'] === 'text') {
                $processedContent[] = [
                    'type' => 'text',
                    'value' => $item['value'],
                ];
            } elseif ($item['type'] === 'image') {
                if ($request->hasFile($item['value'])) {
                    $fileUploaded = $request->file($item['value']);
                    $imageName = 'img_' . $num_padded . '_' . time() . '_' . uniqid() . '.' . $fileUploaded->getClientOriginalExtension();
                    $imagePath = $fileUploaded->storeAs('ExplanationImages', $imageName, 'public');

                    $processedContent[] = [
                        'type' => 'image',
                        'value' => url('storage/' . $imagePath),
                    ];
                }
            } elseif ($item['type'] === 'video') {
                if ($request->hasFile($item['value'])) {
                    $fileUploaded = $request->file($item['value']);
                    $videoName = 'vid_' . $num_padded . '_' . time() . '_' . uniqid() . '.' . $fileUploaded->getClientOriginalExtension();
                    $videoPath = $fileUploaded->storeAs('ExplanationVideos', $videoName, 'public');

                    $processedContent[] = [
                        'type' => 'video',
                        'value' => url('storage/' . $videoPath),
                    ];
                }
            }
        }


        // Save to the database
        $Explanation = explanations::create([
            'SubtopicId' => $request->SubtopicId,
            'ExplanationId' => $ExplanationCountId,
            'Content' => json_encode($processedContent),
        ]);

        if ($Explanation) {
            return response()->json([
                'status' => 200,
                'message' => 'Explanation added successfully',
                'Explanation' => $Explanation,
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Explanation not created',
            ], 500);
        }
    }


    public function AddExamples(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'SubtopicId' => 'required|string|max:191',
            'Text' => 'required|string',
            'Instruction' => 'required|string',
            'Image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            // Check if SubTopicId exists in the topics table
            $subTopicExists = subtopic::where('SubtopicId', $request->SubtopicId)->exists();

            if (!$subTopicExists) {
                return response()->json([
                    'status' => 404,
                    'message' => 'SubtopicId does not exist in the topics table',
                ], 404);
            }

            // Calculate the ExamplesCount
            $ExamplesCount = examples::where('ExampleId', 'like', $request->SubtopicId . '%')->count() + 1;

            // Generate the padded suffix
            $num_padded = sprintf("%03d", $ExamplesCount);
            $ExamplesCountId = $request->SubtopicId . 'exm' . $num_padded;

            if ($request->hasFile('Image')) {
                $fileUploaded = $request->file('Image');
                // Generate a unique file name with ExamplesCountId
                $newQuestionImage = $request->SubtopicId . '_exm' . $ExamplesCount . '.' . $fileUploaded->getClientOriginalExtension();
                $relativePath = $fileUploaded->storeAs('ExamplesPath', $newQuestionImage, 'public');
                $ExamplesPath =  url('storage/' . $relativePath); // Generate the full URL
            } else {
                $ExamplesPath = null; // Set to null or a default value if no image is uploaded
            }

            // Create the subtopic
            $Examples = examples::create([
                'SubtopicId' => $request->SubtopicId,
                'ExampleId' => $ExamplesCountId,
                'Text' => $request->Text,
                'Instruction' => $request->Instruction,
                'Image' => $ExamplesPath,
            ]);

            if ($Examples) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Examples added successfully',
                    'Examples' => $Examples,
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong. Examples not created',
                ], 500); // Fixed status code
            }
        }
    }


    public function AddNarration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'SubtopicId' => 'required|string|max:191',
            'QuestionId' => 'required|string',
            'Content' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $content = json_decode($request->Content, true);
        $processedContent = [];

        foreach ($content as $index => $item) {
            if ($item['type'] === 'text') {
                $processedContent[] = [
                    'type' => 'text',
                    'value' => $item['value'],
                ];
            } elseif (in_array($item['type'], ['image', 'video'])) {
                $fileKey = $item['value']; // Reference the unique key from the content

                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    $fileName = $item['type'] . '_' . $index . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('Narration' . ucfirst($item['type']) . 's', $fileName, 'public');

                    $processedContent[] = [
                        'type' => $item['type'],
                        'value' => url('storage/' . $filePath),
                    ];
                }
            }
        }

        // Save narration entry
        $NarrationCount = narration::where('QuestionId', 'like', $request->QuestionId . '%')->count() + 1;
        $NarrationCountId = $request->QuestionId . rand(1, 999);

        $Narration = narration::create([
            'SubtopicId' => $request->SubtopicId,
            'QuestionId' => $request->QuestionId,
            'NarrationId' => $NarrationCountId,
            'Content' => json_encode($processedContent),
        ]);

        if ($Narration) {
            return response()->json([
                'status' => 200,
                'message' => 'Narration added successfully',
                'Narration' => $Narration,
            ], 200);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Narration not created',
            ], 500);
        }
    }

    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:191',
            'score' => 'required|numeric',
            'subtopicId' => 'nullable|string|max:191',
            'examId' => 'nullable|string|max:191',
            'time_taken' => 'required|string|max:191',
            'class' => 'required|string', // Needed to update leaderboard
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        if ($request->subtopicId && $request->examId) {
            return response()->json([
                'status' => 400,
                'message' => 'Provide either subtopicId or examId, not both.',
            ], 400);
        }

        $username = $request->username;
        $class = $request->class;
        $score = $request->score;

        // Calculate stars from percentage
        $newStars = $score >= 90 ? 3 : ($score >= 70 ? 2 : ($score >= 50 ? 1 : 0));

        // Check if a previous report exists
        $existingReport = report::where('username', $username)
            ->where(function ($query) use ($request) {
                if ($request->subtopicId) {
                    $query->where('SubtopicId', $request->subtopicId);
                }
                if ($request->examId) {
                    $query->where('ExamId', $request->examId);
                }
            })
            ->first();

        if ($existingReport) {
            // Compare scores
            if ($score > $existingReport->Score) {
                $oldStars = $existingReport->Score >= 90 ? 3 : ($existingReport->Score >= 70 ? 2 : ($existingReport->Score >= 50 ? 1 : 0));
                $starDifference = $newStars - $oldStars;

                if ($starDifference > 0) {
                    // Update leaderboard with additional stars
                    $this->updateLeaderboardStars($username, $class, $starDifference);
                }

                // Update report score and time
                $existingReport->update([
                    'Score' => $score,
                    'time_taken' => $request->time_taken,
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Report updated with higher score.',
                    'report' => $existingReport,
                ]);
            } else {
                return response()->json([
                    'status' => 200,
                    'message' => 'No score improvement, no leaderboard update.',
                    'report' => $existingReport,
                ]);
            }
        } else {
            // First time reporting → create report
            report::create([
                'username' => $username,
                'SubtopicId' => $request->subtopicId ?? null,
                'ExamId' => $request->examId ?? null,
                'Score' => $score,
                'time_taken' => $request->time_taken,
            ]);

            // Update leaderboard with new stars
            $this->updateLeaderboardStars($username, $class, $newStars);

            return response()->json([
                'status' => 200,
                'message' => 'Report created and leaderboard updated.',
            ]);
        }
    }

    private function updateLeaderboardStars($username, $class, $starsToAdd)
    {
        $entry = leaderboard::where('username', $username)->first();

        if ($entry) {
            $entry->stars += $starsToAdd;
            $entry->last_practice = now()->toDateString();
            $entry->save();
        } else {
            leaderboard::create([
                'username' => $username,
                'stars' => $starsToAdd,
                'class' => $class,
                'last_practice' => now()->toDateString(),
            ]);
        }
    }


    public function questions(Request $request, $subtopic)
    {
        $request->validate([
            'class' => 'required|string',
        ]);

        $decodedClass = urldecode($request->input('class'));

        // Fetch records for the given class and subtopic
        $filteredRecords = questions::where('class', $decodedClass)
            ->where('subtopic', $subtopic)
            ->get();

        // Check if records exist
        if ($filteredRecords->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Questions fetched successfully',
                'data' => $filteredRecords,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No questions found for the specified class and subtopic!',
            ], 404);
        }
    }

    public function subjects(Request $request)
    {
        // Validate the input
        $request->validate([
            'class' => 'required|string',
        ]);

        // Fetch all records for the given class
        $decodedClass = urldecode($request->input('class')); // Decode the class name if needed
        log::info("This is the class " . $decodedClass);
        $allRecords = subjects::where('class', '=', $decodedClass)->get();

        // Check if records exist
        if ($allRecords->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'data' => $allRecords, // Renamed key to 'data' for clarity
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No records found for the specified class!',
            ], 404); // Use 404 for "not found" errors
        }
    }


    public function topics(Request $request, $subjects)
    {
        // Fetch all records for the given class
        $allRecords = topics::where('Subject', '=', $subjects)->get();

        // Check if records exist
        if ($allRecords->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'data' => $allRecords, // Renamed key to 'data' for clarity
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No records found for the specified subject!',
            ], 404); // Use 404 for "not found" errors
        }
    }

    public function subtopics(Request $request, $topics)
    {
        // Fetch all records for the given class
        $allRecords = subtopic::where('TopicId', '=', $topics)->get();

        // Check if records exist
        if ($allRecords->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'data' => $allRecords, // Renamed key to 'data' for clarity
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No records found for the specified topics!',
            ], 404); // Use 404 for "not found" errors
        }
    }

    public function explanation($subtopicId)
    {
        $explanation = explanations::where('SubtopicId', $subtopicId)->get();

        if ($explanation->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'explanation fetched successfully.',
                'data' => $explanation,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No explanation found for this subtopic.',
            ], 404);
        }
    }
    public function examples($subtopicId)
    {
        $example = examples::where('SubtopicId', $subtopicId)->get();

        if ($example->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'example fetched successfully.',
                'data' => $example,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No example found for this subtopic.',
            ], 404);
        }
    }

    public function narration($QuestionId)
    {
        $narration = narration::where('QuestionId', $QuestionId)->get();

        if ($narration->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'narration fetched successfully.',
                'data' => $narration,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No narration found for this subtopic.',
            ], 404);
        }
    }
    public function leaderboard(Request $request)
    {
        $class = $request->input('class'); // Get class from formData / POST body

        if (!$class) {
            return response()->json([
                'status' => 400,
                'message' => 'Class is required',
            ], 400);
        }

        $allRecords = Leaderboard::where('class', '=', $class)->get();

        if ($allRecords->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'data' => $allRecords,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No records found for the specified class!',
            ], 404);
        }
    }

    public function loginAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $username = $request->username;
        $password = $request->password;

        if ($username === 'FSDGroup' && $password === '12345') {
            $token = Str::random(20);

            return response()->json([
                'status' => 200,
                'message' => 'Login successful',
                'token' => $token,
            ], 200);
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid credentials',
            ], 401);
        }
    }
    public function Editname(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'fullname' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            $usernameExists = Student::where('username', 'like', $request->username);
            if ($usernameExists->exists()) {
                $student = Student::where('username', $request->username)->first();
                $student->fullName = $request->fullname;
                $student->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Name updated successfully',
                    'student' => $student,
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Username not found',
                ], 404);
            }
        }
    }
    public function EditPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $username = $request->username;
        $oldPassword = $request->old_password;
        $newPassword = $request->new_password;

        // Check if the username exists
        $student = Student::where('username', $username)->first();
        if (!$student) {
            return response()->json([
                'status' => 404,
                'message' => 'Username not found',
            ], 404);
        }

        // Check if the old password matches
        if (!Hash::check($oldPassword, $student->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'Old password is incorrect',
            ], 401);
        }

        // Update the password
        $student->password = Hash::make($newPassword);
        $student->save();

        return response()->json([
            'status' => 200,
            'message' => 'Password updated successfully',
        ], 200);
    }
    public function EditEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'email' => 'required|email|unique:students,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $username = $request->username;
        $email = $request->email;

        // Check if new username already exists in students table
            $existingUser = Student::where('email', $email)->first();
            if ($existingUser) {
                return response()->json([
                    'status' => 101, // Custom status code for duplicate username
                    'message' => 'Email already exists. Choose another.',
                ], 101);
            }

        // Check if the username exists
        $student = Student::where('username', $username)->first();
        if (!$student) {
            return response()->json([
                'status' => 404,
                'message' => 'Username not found',
            ], 404);
        }

        // Update the email
        $student->email = $email;
        $student->save();

        return response()->json([
            'status' => 200,
            'message' => 'Email updated successfully',
        ], 200);
    }
    public function EditPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'phone' => 'required|string|unique:students,phone',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $username = $request->username;
        $phone = $request->phone;

        // Check if the username exists
        $student = Student::where('username', $username)->first();
        if (!$student) {
            return response()->json([
                'status' => 404,
                'message' => 'Username not found',
            ], 404);
        }

        // Update the phone number
        $student->phoneNumber = $phone;
        $student->save();

        return response()->json([
            'status' => 200,
            'message' => 'Phone number updated successfully',
        ], 200);
    }
    public function EditProfileImage(Request $request)
    {
        Log::info('All request data', $request->all());
        Log::info('File exists:', ['hasFile' => $request->hasFile('profile_image')]);
        Log::info('File details:', ['file' => $request->file('profile_image')]);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $username = $request->username;

        // Check if the username exists
        $student = Student::where('username', $username)->first();
        if (!$student) {
            return response()->json([
                'status' => 404,
                'message' => 'Username not found',
            ], 404);
        }

        // Handle the profile image upload
        if ($request->hasFile('profile_image')) {
            $fileUploaded = $request->file('profile_image');
            $newProfileImage = $username . '_profile.' . $fileUploaded->getClientOriginalExtension();
            $relativePath = $fileUploaded->storeAs('thumbnails', $newProfileImage, 'public');

            // Update the profile image
            $student->thumbnail = $relativePath;
            $student->save();

            return response()->json([
                'status' => 200,
                'message' => 'Profile image updated successfully',
                'profile_image' => $relativePath,
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No profile image uploaded',
            ], 400);
        }
    }
    public function EditUserClass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'class' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $username = $request->username;
        $class = $request->class;

        // Check if the username exists
        $student = Student::where('username', $username)->first();
        if (!$student) {
            return response()->json([
                'status' => 404,
                'message' => 'Username not found',
            ], 404);
        }

        // Update the class
        $student->class = $class;
        $student->save();

        return response()->json([
            'status' => 200,
            'message' => 'Class updated successfully',
        ], 200);
    }
    public function EditUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_username' => 'required|string',
            'new_username' => 'required|string|unique:students,username',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $old = $request->old_username;
            $new = $request->new_username;

            // Check if new username already exists in students table
            $existingUser = Student::where('username', $new)->first();
            if ($existingUser) {
                return response()->json([
                    'status' => 101, // Custom status code for duplicate username
                    'message' => 'Username already exists. Choose another.',
                ], 101);
            }

            // Confirm the student exists
            $student = Student::where('username', $old)->first();
            if (!$student) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Old username not found',
                ], 404);
            }

            // Update the main student record
            $student->username = $new;
            $student->save();

            // Dynamically get tables that have a 'username' column
            $tables = DB::select("
            SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE COLUMN_NAME = 'username'
              AND TABLE_SCHEMA = DATABASE()
        ");

            foreach ($tables as $table) {
                DB::table($table->TABLE_NAME)
                    ->where('username', $old)
                    ->update(['username' => $new]);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Username updated across all tables',
                'new_username' => $new,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Error updating username',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function refresh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        $username = $request->username;

        // Check if the username exists
        $student = Student::where('username', $username)->first();
        if (!$student) {
            return response()->json([
                'status' => 404,
                'message' => 'Username not found',
            ], 404);
        }

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
                        ],
                    ];
                } else {
                    $response = [
                        'userAbilities' => [
                            [
                                'action' => 'manage',
                                'subject' => 'all',
                            ],
                        ],
                        'userData' => [
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
                        ],
                    ];
                }
                return response()->json([
                    'status' => 200,
                    'message' => 'User data refreshed successfully',
                    'user' => $response,
                ], 200);
    }
}
