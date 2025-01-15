<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\adminController;
use App\Http\Controllers\Api\UserStreakController;


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

// Posts
Route::post('/login', [StudentController::class, 'login']);
Route::post('/logout', [StudentController::class, 'logout']);
Route::post('/AddStudent', [StudentController::class, 'AddStudent']);
Route::post('/AddClasses', [adminController::class, 'AddClasses']);
Route::post('/AddMultipleCountries', [adminController::class, 'AddNewCountries']);
Route::post('/AddQuestions', [adminController::class, 'AddQuestions']);
Route::post('/AddSubjects', [adminController::class, 'AddSubjects']);
Route::post('/AddTopic', [adminController::class, 'AddTopic']);
Route::post('/AddSubTopic', [adminController::class, 'AddSubTopic']);
Route::post('/AddExplanation', [adminController::class, 'AddExplanation']);
Route::post('/AddExamples', [adminController::class, 'AddExamples']);
Route::post('/AddNarration', [adminController::class, 'AddNarration']);
Route::post('/questions/{subtopics}', [adminController ::class, 'questions']);
Route::post('/subjects', [adminController ::class, 'subjects']);
Route::post('/report', [adminController ::class, 'report']);
Route::post('/streaks/update', [UserStreakController ::class, 'updateStreak']);



// Get

Route::get('/FetchAllCountries', [adminController::class, 'FetchAllCountry']);
Route::get('/fetchAllClasses', [adminController::class, 'fetchAllClasses']);
Route::get('/streaks', [UserStreakController::class, 'getStreaks']);
Route::get('/topics/{subjects}', [adminController ::class, 'topics']);
Route::get('/subtopics/{topics}', [adminController ::class, 'subtopics']);
Route::get('/explanation/{subtopics}', [adminController ::class, 'explanation']);
Route::get('/examples/{subtopics}', [adminController ::class, 'examples']);
Route::get('/narration/{questions}', [adminController ::class, 'narration']);
Route::get('/report/{username}', [StudentController ::class, 'report']);
Route::get('/SpecificReport/{username}', [StudentController ::class, 'SpecificReport']);
