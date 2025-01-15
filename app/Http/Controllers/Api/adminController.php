<?php

namespace App\Http\Controllers\Api;

use App\Models\report;
use App\Models\topics;
use App\Models\Classes;
use App\Models\examples;
use App\Models\subjects;
use App\Models\subtopic;
use App\Models\narration;
use App\Models\questions;
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
            $SubTopicCount = subtopic::where('SubtopicId', 'like', $request->Subtopic . '%')->count() + 1;

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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        } else {
            // Ensure either subtopicId or examId is provided, not both
            if ($request->subtopicId && $request->examId) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Provide either subtopicId or examId, not both.',
                ], 400);
            }

            // Find an existing report for the username, subtopicId, or examId
            $existingReport = report::where('username', $request->username)
                ->where(function ($query) use ($request) {
                    if ($request->subtopicId) {
                        $query->where('SubtopicId', $request->subtopicId);
                    }
                    if ($request->examId) {
                        $query->where('ExamId', $request->examId);
                    }
                })
                ->first();

            // If a report exists, compare the scores
            if ($existingReport) {
                if ($request->score > $existingReport->Score) {
                    // Update the existing report if the new score is higher
                    $existingReport->update([
                        'Score' => $request->score,
                        'time_taken' => $request->time_taken,
                    ]);

                    return response()->json([
                        'status' => 200,
                        'message' => 'Report updated successfully with a higher score.',
                        'report' => $existingReport,
                    ], 200);
                } else {
                    // Return a message if the new score is not higher
                    return response()->json([
                        'status' => 200,
                        'message' => 'Existing report has a higher or equal score. No update was made.',
                        'report' => $existingReport,
                    ], 200);
                }
            }

            // Create a new report if none exists
            $newReport = report::create([
                'username' => $request->username,
                'SubtopicId' => $request->subtopicId ?? null,
                'ExamId' => $request->examId ?? null,
                'Score' => $request->score,
                'time_taken' => $request->time_taken,
            ]);

            if ($newReport) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Report submitted successfully.',
                    'report' => $newReport,
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong. Report not created.',
                ], 500);
            }
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
}
