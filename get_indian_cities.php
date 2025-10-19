<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get state from query parameter
    $state = $_GET['state'] ?? '';
    
    if (empty($state)) {
        echo json_encode(['error' => 'State parameter is required']);
        exit;
    }
    
    // Indian states and their major cities
    $stateCities = [
        'Andhra Pradesh' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Nellore', 'Kurnool', 'Tirupati', 'Kadapa', 'Anantapur', 'Rajahmundry', 'Kakinada'],
        'Arunachal Pradesh' => ['Itanagar', 'Naharlagun', 'Pasighat', 'Tezpur', 'Dibrugarh', 'Tinsukia', 'Jorhat', 'Sivasagar', 'Guwahati', 'Silchar'],
        'Assam' => ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Tezpur', 'Nagaon', 'Tinsukia', 'Sivasagar', 'Barpeta', 'Karimganj'],
        'Bihar' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Darbhanga', 'Purnia', 'Ara', 'Begusarai', 'Katihar', 'Chapra'],
        'Chhattisgarh' => ['Raipur', 'Bhilai', 'Bilaspur', 'Korba', 'Rajnandgaon', 'Durg', 'Raigarh', 'Ambikapur', 'Jagdalpur', 'Chirmiri'],
        'Goa' => ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda', 'Mormugao', 'Curchorem', 'Valpoi', 'Bicholim', 'Sanguem'],
        'Gujarat' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Junagadh', 'Gandhinagar', 'Nadiad', 'Morbi'],
        'Haryana' => ['Faridabad', 'Gurgaon', 'Panipat', 'Ambala', 'Yamunanagar', 'Rohtak', 'Hisar', 'Karnal', 'Sonipat', 'Panchkula'],
        'Himachal Pradesh' => ['Shimla', 'Mandi', 'Solan', 'Dharamshala', 'Baddi', 'Palampur', 'Nahan', 'Una', 'Chamba', 'Kullu'],
        'Jharkhand' => ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro', 'Deoghar', 'Phusro', 'Hazaribagh', 'Giridih', 'Ramgarh', 'Medininagar'],
        'Karnataka' => ['Bangalore', 'Mysore', 'Hubli', 'Mangalore', 'Belgaum', 'Gulbarga', 'Davanagere', 'Bellary', 'Bijapur', 'Shimoga'],
        'Kerala' => ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Palakkad', 'Alappuzha', 'Malappuram', 'Kannur', 'Kasaragod'],
        'Madhya Pradesh' => ['Bhopal', 'Indore', 'Gwalior', 'Jabalpur', 'Ujjain', 'Sagar', 'Dewas', 'Satna', 'Ratlam', 'Rewa'],
        'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Aurangabad', 'Solapur', 'Amravati', 'Kolhapur', 'Sangli'],
        'Manipur' => ['Imphal', 'Thoubal', 'Bishnupur', 'Churachandpur', 'Ukhrul', 'Senapati', 'Tamenglong', 'Chandel', 'Jiribam', 'Kakching'],
        'Meghalaya' => ['Shillong', 'Tura', 'Jowai', 'Nongstoin', 'Nongpoh', 'Williamnagar', 'Baghmara', 'Mairang', 'Mankachar', 'Amlarem'],
        'Mizoram' => ['Aizawl', 'Lunglei', 'Saiha', 'Champhai', 'Kolasib', 'Serchhip', 'Lawngtlai', 'Mamit', 'Saitual', 'Hnahthial'],
        'Nagaland' => ['Kohima', 'Dimapur', 'Mokokchung', 'Tuensang', 'Wokha', 'Mon', 'Phek', 'Zunheboto', 'Kiphire', 'Longleng'],
        'Odisha' => ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur', 'Puri', 'Balasore', 'Bhadrak', 'Baripada', 'Jharsuguda'],
        'Punjab' => ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali', 'Firozpur', 'Batala', 'Pathankot', 'Moga'],
        'Rajasthan' => ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Bikaner', 'Ajmer', 'Bharatpur', 'Alwar', 'Bhilwara', 'Sikar'],
        'Sikkim' => ['Gangtok', 'Namchi', 'Mangan', 'Gyalshing', 'Singtam', 'Rangpo', 'Jorethang', 'Ravangla', 'Pakyong', 'Lachen'],
        'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli', 'Tiruppur', 'Erode', 'Vellore', 'Thoothukudi'],
        'Telangana' => ['Hyderabad', 'Warangal', 'Nizamabad', 'Khammam', 'Karimnagar', 'Ramagundam', 'Mahbubnagar', 'Nalgonda', 'Adilabad', 'Suryapet'],
        'Tripura' => ['Agartala', 'Dharmanagar', 'Udaipur', 'Ambassa', 'Kailashahar', 'Belonia', 'Khowai', 'Teliamura', 'Sabroom', 'Sonamura'],
        'Uttar Pradesh' => ['Lucknow', 'Kanpur', 'Ghaziabad', 'Agra', 'Meerut', 'Varanasi', 'Allahabad', 'Bareilly', 'Aligarh', 'Moradabad'],
        'Uttarakhand' => ['Dehradun', 'Haridwar', 'Rishikesh', 'Roorkee', 'Kashipur', 'Rudrapur', 'Haldwani', 'Nainital', 'Almora', 'Pithoragarh'],
        'West Bengal' => ['Kolkata', 'Asansol', 'Siliguri', 'Durgapur', 'Bardhaman', 'Malda', 'Bahraich', 'Habra', 'Kharagpur', 'Shantipur'],
        'Delhi' => ['New Delhi', 'Central Delhi', 'East Delhi', 'North Delhi', 'South Delhi', 'West Delhi', 'South West Delhi', 'North East Delhi', 'North West Delhi', 'Shahdara'],
        'Jammu and Kashmir' => ['Srinagar', 'Jammu', 'Anantnag', 'Baramulla', 'Sopore', 'Kathua', 'Rajouri', 'Poonch', 'Doda', 'Kishtwar'],
        'Ladakh' => ['Leh', 'Kargil', 'Nubra', 'Drass', 'Zanskar', 'Padum', 'Khalatse', 'Chumathang', 'Nimu', 'Hemis']
    ];
    
    // Get cities for the specified state
    $cities = $stateCities[$state] ?? [];
    
    if (empty($cities)) {
        echo json_encode(['error' => 'No cities found for this state']);
        exit;
    }
    
    // Return cities as JSON
    echo json_encode(['cities' => $cities]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
