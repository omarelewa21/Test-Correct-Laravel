<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use tcCore\TestTakeStatus;

class UpdateTestParticipantRequest extends Request {

	/**
	 * @var TestParticipant
	 */
	private $testParticipant;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->testParticipant = $route->getParameter('test_participant');
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{

		$roles = $this->getUserRoles();
		if (in_array('Student', $roles) && !in_array('Teacher', $roles) && !in_array('Invigilator', $roles)) {
			return $this->testParticipant->getAttribute('user_id') == Auth::id();
		} else {
			//Todo check if Invigilator
			return true;
		}
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'test_take_id' => 'sometimes',
			'user_id' => 'sometimes',
			'test_take_status_id' => 'sometimes|exists:test_take_statuses,id|in:'.implode(',',$this->getAllowedTestTakeStatusIds()),
			'note' => ''
		];
	}

	public function getAllowedTestTakeStatusIds()
	{
		$roles = $this->getUserRoles();
		$statusses = TestTakeStatus::lists('id', 'name')->all();
		$status = $this->testParticipant->testTakeStatus->name;
		if (in_array('Student', $roles) && $this->testParticipant->getAttribute('user_id') == Auth::id()) {
			if ($status === 'Planned' || $status === 'Test not taken') {
				$results = [$statusses[$status], $statusses['Taking test']];
			} elseif ($status === 'Taking test') {
				$results = [$statusses[$status], $statusses['Handed in']];
			} elseif ($status === 'Handed in' || $status === 'Taken') {
				$results = [$statusses[$status], $statusses['Discussing']];
			} else {
				$results = [];
			}
		} elseif (in_array('Teacher', $roles) || in_array('Invigilator', $roles)) {
			if ($status === 'Planned') {
				$results = [$statusses['Planned'], $statusses['Taking test'], $statusses['Taken away']];
			} elseif ($status === 'Taking test') {
				$results = [$statusses['Taking test'], $statusses['Taken'], $statusses['Taken away']];
			} elseif ($status === 'Taken away') {
				$results = [$statusses['Taken away'], $statusses['Taking test']];
			} elseif ($status === 'Handed in') {
				$results = [$statusses['Handed in'], $statusses['Taking test'], $statusses['Discussing']];
			} elseif ($status === 'Taken') {
				$results = [$statusses['Taken'], $statusses['Taking test'], $statusses['Discussing']];
			} else {
				$results = [];
			}
		}

		// If school location of the test participant is not activated, do not allow switching to state Taking test of Discussing.
		$activated = $this->testParticipant->schoolClass->schoolLocation->getAttribute('activated');
		if ($activated != true) {
			if(($key = array_search($statusses['Taking test'], $results)) !== false) {
				unset($results[$key]);
			}

			if(($key = array_search($statusses['Discussing'], $results)) !== false) {
				unset($results[$key]);
			}
		}

		return $results;
	}

	/**
	 * Get the sanitized input for the request.
	 *
	 * @return array
	 */
	public function sanitize()
	{
		return $this->all();
	}

}
