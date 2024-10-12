<?php

namespace App\Http\Controllers\Api;

use App\Models\member;
use App\Models\children;
use Illuminate\Http\Request;
use Ramsey\Uuid\Type\Integer;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class childrenController extends Controller
{

    public function FetchAllChildren($UserId)
    {
        $Children = children::all();

        if ($Children->count() > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'children' => $Children,
            ],200);
        } else {
            return response()->json([
                'status' => 404,
                'message ' => 'No records found!',
            ], 200);
        }
    }

    public function AddChild(Request $request)
    {
        // Validation for ParentId
        $validator = Validator::make($request->all(), [
            'ParentId' => 'required|string|max:191',
            'children' => 'required|array', // Ensure children is an array
            'children.*.email' => 'required|string|email|max:191',
            'children.*.password' => 'required|string|min:6|max:191',
            'children.*.sname' => 'required|string|max:191',
            'children.*.fname' => 'required|string|max:191',
            'children.*.mname' => 'nullable|string|max:191',
            'children.*.Gender' => 'required|string|max:191',
            'children.*.dob' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);
        }

        // Check if ParentId exists in the member table
        $member = member::where('UserId', '=', $request->ParentId)->first();

        if ($member) {
            // Loop through each child and insert into the children table
            foreach ($request->children as $childData) {
                children::create([
                    'ParentId' => $request->ParentId,
                    'email' => $childData['email'],
                    'password' => Hash::make($childData['password']), // Hash the password before saving
                    'sname' => $childData['sname'],
                    'fname' => $childData['fname'],
                    'mname' => $childData['mname'],
                    'Gender' => $childData['Gender'],
                    'dob' => $childData['dob'],
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Child details added successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'ParentId not registered !!!',
            ], 404);
        }
    }


    public function deleteChildren($parentid,$id){

        $member = member::where('UserId', '=', $parentid)->first();

        if ($member) {

        $child = children::where('id', '=', $id)->where('ParentId', '=', $parentid)->first();
        if($child){
             $child->delete();
             return response()->json([
                'status' => 200,
                'message' => 'Child details deleted successfully'
            ], 200);

        }else {
            return response()->json([
                'status' => 404,
                'message' => 'Child with id of number '.$id.' not found or registered for parentid of '.$parentid,
            ], 404);
        }



        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User/Parent not found or registered',
            ], 404);
        }


    }

    public function updateChild(Request $request,Int $id)
    {
        $validator = Validator::make($request->all(), [
            'ParentId' => 'required|string|max:191',
            'sname' => 'required|string|max:191',
            'fname' => 'required|string|max:191',
            'mname' => 'required|string|max:191',
            'Gender' => 'required|string|max:191',
            'dob' => 'required|string|max:191',
            'ministry' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages(),
            ], 422);

        } else {

            $member = member::where('UserId', '=', $request->ParentId)->first();

            if ($member) {
                $child = children::where('id', '=', $id)->where('ParentId', '=', $request->ParentId)->first();

                if($child){
                    $child->update([
                        'ParentId' => $request->ParentId,
                        'sname' => $request->sname,
                        'fname' => $request->fname,
                        'mname' => $request->mname,
                        'Gender' => $request->Gender,
                        'dob' => $request->dob,
                        'ministry' => $request->ministry,
                    ]);
                            return response()->json([
                            'status' => 200,
                            'message' => 'Child details updated successfully'
                        ], 200);

               }else {
                   return response()->json([
                       'status' => 404,
                       'message' => 'Child with id of number '.$id.' not found or registered for parentid of '.$request->ParentId,
                   ], 404);
               }

            } else {

                    return response()->json([
                        'status' => 500,
                        'message' => 'Update failed as parent  is not registered',
                    ], 200);

            }
        }

    }

    public function viewchildren($parentid){
        $children = children::where('ParentId', $parentid)->get();
        if ($children) {
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'children' => $children,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    }


    public function viewchild($parentid){

        $child = children::with('member')->where('ParentId', '=', $parentid)->get();
        if($child){
            return response()->json([
                'status' => 200,
                'message' => 'Record fetched successfully',
                'children' => $child,
            ], 200);

        }else {
            return response()->json([
                'status' => 404,
                'message' => 'Child with id of number '.$parentid.' not found or registered for parentid of '.$parentid,
            ], 404);
        }







    }
}
