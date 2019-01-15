<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;

class CustomerController extends BaseController
{
    public function index() {
	    $limit = 100;
	    $data = Customer::paginate($limit);
	    $breadcrumb = ['Customers' => ''];
	    return view('customers-list', ['users' => $data, 'breadcrumb' => $breadcrumb]);
    }

	public function view($id) {
		$customer = Customer::find($id);
		if (!$customer) {
			return view('admin.404');
		}

		$customerDetails = \DB::collection('customer_details')->where('customer_id', '=', $customer->customer_id)->first();
		$orders = Order::where('customer_id', $customer->customer_id)->get();

		$breadcrumb = ['Customers' => url('customers'), $customer->customer_name => ''];

		return view('customer-view', ['customer' => $customer,
		                              'customer_details' => $customerDetails,
		                              'breadcrumb' => $breadcrumb,
										'orders' => $orders]);
	}

    public function profiler() {
	    $data = \DB::collection('customer_profiler_data')->get();
	    $breadcrumb = ['Customers profiler result' => ''];

	    return view('profiler-list', ['profiles' => $data, 'breadcrumb' => $breadcrumb]);
    }

	public function profiler_response($id) {
		$data = \DB::collection('customer_profiler_data')->find($id);
		$breadcrumb = ['Customers profiler result' => url('customers/profiler'), $id => ''];

		$questions = [];
		foreach (json_decode(self::GetQuestions()) as $questionSet) {
			if(is_array($questionSet)) {
				foreach ($questionSet as $question) {
					$questions[$question->key] = $question;
				}
			}
		}

		return view('profiler-response', ['response' => $data, 'questions' => $questions, 'breadcrumb' => $breadcrumb]);
	}

	public static function GetQuestions() {
    	return '
    	    [
	[{
			"title": "Name",
			"key": "name",
			"type": "text",
			"options": {}
		},
		{
			"title": "Date of Birth",
			"key": "dob",
			"type": "text",
			"options": {}
		}, {
			"title": "Age",
			"key": "age",
			"type": "text",
			"options": {}
		},
		{
			"title": "Contact Number",
			"key": "contact",
			"type": "text",
			"options": {}
		},
		{
			"title": "Email ID",
			"key": "email_id",
			"type": "text",
			"options": {}
		},
		{
			"title": "Spouse Name",
			"key": "spouse_name",
			"type": "text",
			"options": {}
		},
		{
			"title": "Date of Birth",
			"key": "spouse_dob",
			"type": "text",
			"options": {}
		},
		{
			"title": "Age",
			"key": "spouse_age",
			"type": "text",
			"options": {}
		},
		{
			"title": "Contact Number",
			"key": "spouse_contact",
			"type": "text",
			"options": {}
		},
		{
			"title": "Spouse Email ID",
			"key": "spouse_email_id",
			"type": "text",
			"options": {}
		},
		{
			"title": "City of Residence",
			"key": "city",
			"type": "text",
			"options": {}
		},
		{
			"title": "Residence Type",
			"key": "residence",
			"type": "options",
			"options": {
				"A": "Owned House",
				"B": "Rented House",
				"C": "Ancestral House",
				"D": "Company provided residence"
			}
		},
		{
			"title": "Preferred language of communication",
			"key": "language",
			"type": "options",
			"options": {
				"A": "English",
				"B": "Hindi"
			}
		},
		{
			"title": "Highest level of education completed",
			"key": "education_type",
			"type": "options",
			"options": {
				"A": "High School",
				"B": "Intermediate",
				"C": "Graduate",
				"D": "Post Graduate",
				"E": "Phd"
			}
		},
		{
			"title": "Degree Obtained",
			"key": "education",
			"type": "text",
			"options": {}
		},
		{
			"title": "Spouse - Highest level of education completed",
			"key": "spouse_education_type",
			"type": "options",
			"options": {
				"A": "High School",
				"B": "Intermediate",
				"C": "Graduate",
				"D": "Post Graduate",
				"E": "Phd"
			}
		},
		{
			"title": "Spouse - Degree Obtained",
			"key": "spouse_education",
			"type": "text",
			"options": {}
		},
		{
			"title": "Number of children",
			"key": "children",
			"type": "options",
			"options": {
				"A": "1",
				"B": "2",
				"C": "3"
			}
		},
		{
			"title": "Children studying in AES",
			"key": "children_aes",
			"type": "text",
			"options_desc": {}
		},
		{
			"title": "Source of Income",
			"key": "income_source",
			"type": "options",
			"options": {
				"A": {
					"title": "Salaried",
					"key": "income_type",
					"type": "options",
					"options": {
						"A": "Government",
						"B": "Non-government",
						"C": "Housewife/Husband"
					}
				},
				"B": {
					"title": "Business",
					"key": "income_type",
					"type": "options",
					"options": {
						"A": "Owned Business",
						"B": "Partnership in Business"
					}
				}
			}
		},
		{
			"title": "Organization",
			"key": "income_org",
			"type": "text",
			"options": {}
		},
		{
			"title": "Profession",
			"key": "income_details",
			"type": "options",
			"options": {
				"A": "Engineer",
				"B": "Doctor",
				"C": "CA",
				"D": "Lawyer",
				"E": "Teacher"
			}
		},
		{
			"title": "Spouse - Source of Income",
			"key": "spouse_income_source",
			"type": "options",
			"options": {
				"A": {
					"title": "Salaried",
					"key": "spouse_income_type",
					"type": "options",
					"options": {
						"A": "Government",
						"B": "Non-government",
						"C": "Housewife/Husband"
					}
				},
				"B": {
					"title": "Business",
					"key": "spouse_income_type",
					"type": "options",
					"options": {
						"A": "Owned Business",
						"B": "Partnership in Business"
					}
				}
			}
		},
		{
			"title": "Organization",
			"key": "spouse_income_org",
			"type": "text",
			"options": {}
		},
		{
			"title": "Profession",
			"key": "spouse_income_details",
			"type": "options",
			"options": {
				"A": "Engineer",
				"B": "Doctor",
				"C": "CA",
				"D": "Lawyer",
				"E": "Teacher"
			}
		},
		{
			"title": "Cars Owned",
			"key": "car",
			"type": "text",
			"options": {}
		}
	],
	[{
			"title": "Children Profiling",
			"key": "children_profile",
			"type": "locale",
			"options": {
				"1": " I strongly agree",
				"2": " I agree",
				"3": " I am not sure",
				"4": " I disagree",
				"5": " I strongly disagree"
			},
			"options_locale": {
				"children_profile_1": "My children are highly interested in learning new things.",
				"children_profile_2": "My children take initiative to learn new things.",
				"children_profile_3": "My children are confident about learning new things.",
				"children_profile_4": "My children understand the importance of fitness.",
				"children_profile_5": "My children are interested in sports.",
				"children_profile_6": "My children are involved in school related activities apart from studies.",
				"children_profile_7": "My children actively participate in social events and competitions.",
				"children_profile_8": "Extracurricular/sports activities are important for children to engage into.",
				"children_profile_9": "Extracurricular/sports activities help a children in building confidence.",
				"children_profile_10": "Extracurricular/sports activities help children in developing a better and a strong personality.",
				"children_profile_11": "Engaging in extracurricular/sports activities help children to a certain extent in evaluating their career options.",
				"children_profile_12": "Engaging in extracurricular/sports activities help children in developing/improving their social and interpersonal skills.",
				"children_profile_13": "Engaging in extracurricular/sports activities helps in bringing a sense of discipline in children.",
				"children_profile_14": "Engaging in extracurricular/sports activities affects the academic performance of children."
			}
		},
		{
			"title": "If the school started different extracurricular activities, then would you enrol your child/children for the same?",
			"key": "children_profile_15",
			"type": "options",
			"options": {
				"Y": "Yes",
				"N": "No"
			}
		},
		{
			"title": "Quality time spent with child/children?",
			"key": "children_profile_16",
			"type": "options",
			"options": {
				"A": "1-2 hours",
				"B": "2-4 hours",
				"C": "more than 4 hours"
			}
		},
		{
			"title": "Activities on which quality time spent with child/children",
			"key": "children_profile_17",
			"type": "multiple_options",
			"options": {
				"A": "Talking/Discussing",
				"B": "Sports",
				"C": "Outings",
				"D": "Studying"
			}
		},
		{
			"title": "Spouses quality time spent with child/children?",
			"key": "children_profile_18",
			"type": "options",
			"options": {
				"A": "1-2 hours",
				"B": "2-4 hours",
				"C": "more than 4 hours"
			}
		},
		{
			"title": "Activities on which spouse spents quality time spent with child/children",
			"key": "children_profile_19",
			"type": "multiple_options",
			"options": {
				"A": "Talking/Discussing",
				"B": "Sports",
				"C": "Outings",
				"D": "Studying"
			}
		},
		{
			"title": "Aspirations for your child/children’s future education",
			"key": "children_profile_20",
			"type": "options",
			"options": {
				"A": "Post Graduation",
				"B": "PhD",
				"C": "Medical",
				"D": "Law",
				"E": "Study Abroad",
				"F": "Economics",
				"G": "Engineering"
			}
		},
		{
			"title": "Occupation you prefer/aspire for your child to take",
			"key": "children_profile_21",
			"type": "options",
			"options": {
				"A": "Engineer",
				"B": "Doctor",
				"C": "CA/CS",
				"D": "Lawyer"
			}
		},
		{
			"title": "Foreign languages to learn",
			"key": "children_profile_22",
			"type": "options",
			"options": {
				"A": "French",
				"B": "Spanish",
				"C": "German",
				"D": "Latin"
			}
		},
		{
			"title": "Foreign languages to learn",
			"key": "children_profile_23",
			"type": "options",
			"options": {
				"A": "French",
				"B": "Spanish",
				"C": "German",
				"D": "Latin"
			}
		},
		{
			"title": "Extra-curricular activities parent choosed to pay for",
			"key": "extracurricular_activities",
			"type": "multiple_options",
			"options": {
				"A": " 3D printing",
				"B": " abacus",
				"C": " art and craft",
				"D": " calligraphy",
				"E": " clay modelling",
				"F": " dance",
				"G": " experimental science",
				"H": " gardening",
				"I": " handwriting correction/concentration development",
				"J": " instrumental music",
				"K": " movie making",
				"L": " non flammable cooking",
				"M": " paper toy making/paper mache",
				"N": " personality development and public speaking",
				"O": " photography",
				"P": " pottery",
				"Q": " robotics",
				"R": " super learning/memory tricks",
				"S": " theatre",
				"T": " vocal music"
			}
		},
		{
			"title": "How much parent would prefer to pay for extracurricular activities",
			"key": "children_profile_24",
			"type": "options",
			"options": {
				"A": " less than 1000",
				"B": " 1001-1500",
				"C": " 1501-2000",
				"D": " 2001-2500",
				"E": " 2501-2000",
				"F": " 3001-3500"
			}
		},
		{
			"title": "Sports activities parent choosed to pay for",
			"key": "sports_activities",
			"type": "multiple_options",
			"options": {
				"A": " badminton",
				"B": " basketball",
				"C": " chess",
				"D": " cricket",
				"E": " fencing",
				"F": " football",
				"G": " short tennis",
				"H": " skating",
				"I": " swimming",
				"J": " table tennis",
				"K": " taekwondo/wushu/karate",
				"L": " volleyball",
				"M": " yoga"
			}
		},
		{
			"title": "How much parent would prefer to pay for sports activities",
			"key": "children_profile_25",
			"type": "options",
			"options": {
				"A": " less than 1000",
				"B": " 1001-1500",
				"C": " 1501-2000",
				"D": " 2001-2500",
				"E": " 2501-2000",
				"F": " 3001-3500"
			}
		}
	],
	[{
			"title": "Music Preference Choosen",
			"key": "lifestyle_profile_1",
			"type": "multiple_options",
			"options": {
				"N": "None",
				"A": "Indian classical",
				"B": "Ghazal",
				"C": "Pop/Rock",
				"D": "Bollywood songs",
				"E": "Instrumental"
			}
		},
		{
			"title": "Movie Preference Choosen",
			"key": "lifestyle_profile_2",
			"type": "multiple_options",
			"options": {
				"N": "None",
				"A": "English movies",
				"B": "Hindi Movies"
			}
		},
		{
			"title": "Reading Preference Choosen",
			"key": "lifestyle_profile_3",
			"type": "multiple_options",
			"options": {
				"N": "None",
				"A": "Fiction",
				"B": "Non-fiction",
				"C": "Magazines/Periodicals"
			}
		},
		{
			"title": "Theatre Preference Choosen",
			"key": "lifestyle_profile_4",
			"type": "multiple_options",
			"options": {
				"N": "No",
				"A": "English",
				"B": "Hindi"
			}
		},
		{
			"title": "Sports Preference Choosen",
			"key": "lifestyle_profile_5",
			"type": "multiple_options",
			"options": {
				"N": "No",
				"A": "Cricket",
				"B": "Tennis",
				"C": "Football",
				"D": "Hockey",
				"E": "Badminton",
				"F": "Golf"
			}
		},
		{
			"title": "Only follow these sports",
			"key": "lifestyle_profile_6",
			"type": "options",
			"options": {
				"N": "None",
				"A": "Cricket",
				"B": "Tennis",
				"C": "Football",
				"D": "Hockey",
				"E": "Badminton",
				"F": "Golf"
			}
		},
		{
			"title": "Travel Preference choosen",
			"key": "lifestyle_profile_7a",
			"type": "options",
			"options": {
				"N": "No",
				"A": "Business",
				"B": "Adventure",
				"C": "Sight-seeing",
				"D": "Religion and pilgrimage",
				"E": "Relaxation"
			}
		},
		{
			"title": "How often family goes on trips (India/Abroad)",
			"key": "lifestyle_profile_7b",
			"type": "options",
			"options": {
				"A": "Once in 3 months",
				"B": "Once in 6 months",
				"C": "Yearly"
			}
		},
		{
			"title": "How often family goes out for a meal outside with your family/friends/relatives",
			"key": "lifestyle_profile_8",
			"type": "options",
			"options": {
				"A": "Weekends",
				"B": "Once/Twice in a month"
			}
		},
		{
			"title": "Favourite Cuisines",
			"key": "lifestyle_profile_9",
			"type": "multiple_options",
			"options": {
				"A": " punjabi",
				"B": " mughlai",
				"C": " south indian",
				"D": " gujrati",
				"E": " chinese",
				"F": " thai",
				"G": " italian",
				"H": " french",
				"I": " chaat/indian snacks"
			}
		},
		{
			"title": "Club Memberships",
			"key": "lifestyle_profile_10",
			"type": "options",
			"options": {
				"Y": " Yes",
				"N": " No"
			}
		}
	]
]
    	';
	}
}
