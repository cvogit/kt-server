<?php

namespace App\Http\Controllers;

use App\Consultant;
use App\User;
use App\Report;
use App\Student;
use App\StudentImage;
use App\Manager;
use App\BasicForm;
use App\PregnancyForm;
use App\BirthForm;
use App\InfancyForm;
use App\ToddlerForm;
use App\FamilyForm;
use App\IllnessForm;
use App\EducationForm;
use App\PresentForm;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class ConsultantController extends Controller
{
	/**
	 * Return a consultant detail
	 *
	 * @param \Illuminate\Http\Request
	 * @param integer
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function get(Request $request, $consultantId)
	{	
		// Validate $id
		if ( !$this->req->isValidInt($consultantId) )
			return response()->json(['message' => 'The request parameters are invalid.'], 404);

		// Find consultant to be return
		$consultant = Consultant::find($consultantId);

		if ( !$consultant )
			return response()->json(['message' => 'Unable to find consultant.'], 404);

		return response()->json([
			'message' => "Succesfully fetch consultant.",
			'result' 	=> $consultant
			], 200);
	}

	/**
	 * Return all consultants
	 *
	 * @param \Illuminate\Http\Request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getList(Request $request)
	{	
		// Validate request
		$this->validate($request, [
			'offset' 		=> 'integer',
			]);

		$offset = 0;
		$limit  = 20;

		if ( $request->has('offset') )
			$offset = $request->input('offset');

		$consultants = Consultant::skip($offset)->take($limit)->get();

		return response()->json([
			'message' => "Succesfully fetch all consultants.",
			'result' 	=> $consultants,
			'offset'	=> $offset+$limit
			], 200);
	}

	/**
	 * Return consultant initial resources
	 *
	 * @param \Illuminate\Http\Request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getConsultantResource(Request $request)
	{

		$user = $this->req->getUser();

		$consultant = Consultant::where('userId', $user->id)->get(['id', 'userId']);
		$consultant = $consultant[0];

		if(!$consultant) {
			return response()->json(['message' => 'Unable to find consultant.'], 404);
		}

		// Get list of managers resource
		$managersId = Manager::get(['userId']);
		$managers = [];
		foreach ($managersId as $manager) {
			array_push($managers, User::where('id', $manager->userId)->get(['id', 'firstName','lastName', 'phoneNum', 'lastLogin', 'avatarId'])[0]);
		}
		$managers = $managers;

		// Get all students
		$students = Student::where('active', 1)->get();

		// Query students resource
		foreach ($students as $student) {
			// Query student image list
			$student->images = StudentImage::Where('studentId', $student->id)->get(['imageId']);

			// Query student report list
			$student->reports = Report::Where('studentId', $student->id)->get();

			// For each report, get student name
			foreach ($student->reports as $report) {
				$report->student = Student::Where('id', $report->studentId)->get(['name'])->first();
			}

			// Query all of student forms
			$forms = array(
				'basicForm'	=> BasicForm::Where('id', $student->basicFormId)->get(['question_1', 'question_2', 'question_3', 'question_4', 'question_5', 'question_6', 'question_7', 'question_8', 'question_9']),
				'familyForm'=> FamilyForm::Where('id', $student->familyFormId)->get(['question_1', 'question_2', 'question_3', 'question_4', 'question_5', 'question_6', 'question_7', 'question_8', 'question_9', 'question_10', 'question_11', 'question_12']),
				'pregnancyForm'=> PregnancyForm::Where('id', $student->pregnancyFormId)->get(['question_1', 'question_2', 'question_3', 'question_4', 'question_5', 'question_6', 'question_7', 'question_8', 'question_9', 'question_10', 'question_11', 'question_12', 'question_13', 'question_14', 'question_15']),
				'birthForm'		=> 	BirthForm::Where('id', $student->birthFormId)->get(['question_1', 'question_2', 'question_3', 'question_4']),
				'infancyForm'	=> 	InfancyForm::Where('id', $student->infancyFormId)->get(['question_1', 'question_2', 'question_3', 'question_4', 'question_5', 'question_6', 'question_7']),
				'toddlerForm'	=> 	ToddlerForm::Where('id', $student->toddlerFormId)->get(['question_1', 'question_2', 'question_3', 'question_4', 'question_5', 'question_6', 'question_7', 'question_8', 'question_9', 'question_10', 'question_11', 'question_12', 'question_13', 'question_14', 'question_15', 'question_16', 'question_17', 'question_18', 'question_19', 'question_20', 'question_21', 'question_22']),
				'illnessForm'	=> 	IllnessForm::Where('id', $student->illnessFormId)->get(['question_1', 'question_2', 'question_3', 'question_4', 'question_5', 'question_6', 'question_7', 'question_8', 'question_9', 'question_10', 'question_11', 'question_12', 'question_13', 'question_14', 'question_15', 'question_16', 'question_17', 'question_18', 'question_19', 'question_20', 'question_21', 'question_22', 'question_23', 'question_24', 'question_25', 'question_26', 'question_27', 'question_28', 'question_29', 'question_30', 'question_31']),
				'educationForm'=> EducationForm::Where('id', $student->educationFormId)->get(['question_1', 'question_2', 'question_3', 'question_4', 'question_5', 'question_6']),
				'presentForm'	=> 	PresentForm::Where('id', $student->presentFormId)->get(['question_1', 'question_2', 'question_3', 'question_4']),
				);
			$student->forms = $forms;
		}

		// Get all teacher reports
		$reports = Report::Where('userId', $user->id)->get();

		return response()->json([
			'message' => "Succesfully fetch consultant resources.",
			'result' 	=> array(
					'managers' 	=> $managers,
					'students' 	=> $students,
					'reports'		=> $reports,
				)
			], 200);
	}
}