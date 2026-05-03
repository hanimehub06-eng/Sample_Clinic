<?php
$doctors = [
  'bongon' => [
    'id'        => 'bongon',
    'name'      => 'Dr. Bongon',
    'full_name' => 'Dr. Beethoven N. Bongon',
    'specialty' => 'Internal Medicine',
    'photo'     => 'imgs/dr-bongon.png',
    'bio'       => 'Dr. Beethoven Bongon is in the field of Internal Medicine. Our doctor treats patients at St. Vincent General Hospital in Cebu City, Cebu. Patients are accepted by appointment.',
    'schedule'  => [
      'Monday'    => '8am - 4pm',
      'Tuesday'   => '8am - 4pm',
      'Wednesday' => '8am - 4pm',
      'Thursday'  => '8am - 4pm',
      'Friday'    => '8am - 4pm',
    ],
  ],
  'nakaegawa' => [
    'id'        => 'nakaegawa',
    'name'      => 'Dr. Nakaegawa',
    'full_name' => 'Dr. Hiroshi A. Nakaegawa',
    'specialty' => 'Cardiology',
    'photo'     => 'imgs/dr-nakaegawa.png',
    'bio'       => 'Dr. Hiroshi Nakaegawa is a specialist in Cardiology with over 10 years of experience diagnosing and treating heart conditions. He sees patients at Medi Clinic and is recognized for his thorough, evidence-based, and compassionate approach to cardiovascular care. Patients are accepted by appointment.',
    'schedule'  => [
      'Monday'    => '10am - 5pm',
      'Tuesday'   => '10am - 5pm',
      'Wednesday' => '10am - 5pm',
      'Thursday'  => '8am - 4pm',
      'Friday'    => '8am - 4pm',
    ],
  ],
  'dacumos' => [
    'id'        => 'dacumos',
    'name'      => 'Dr. Dacumos',
    'full_name' => 'Dr. Maria L. Dacumos',
    'specialty' => 'Pediatrics',
    'photo'     => 'imgs/dr-dacumos.png',
    'bio'       => 'Dr. Maria Dacumos is a dedicated Pediatrician with extensive experience in child and adolescent healthcare. She provides comprehensive medical services for infants, children, and teenagers at Medi Clinic, and is known for her gentle and family-centered approach to care. Patients are accepted by appointment.',
    'schedule'  => [
      'Monday'    => '8am - 4pm',
      'Tuesday'   => '10am - 6pm',
      'Wednesday' => '8am - 4pm',
      'Thursday'  => '10am - 6pm',
      'Friday'    => '8am - 4pm',
    ],
  ],
];

$time_slots = [
  '8am - 9am',
  '10am - 11am',
  '12pm - 1pm',
  '2pm - 3pm',
  '4pm - 5pm',
];

$appointments = [
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Abella, Gabriel Rey C.', 'status' => 'Confirmed', 'doctor' => 'Dr. Bongon'],
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Presto, Vin Hendrix M.', 'status' => 'Pending', 'doctor' => 'Dr. Bongon'],
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Ignacio, Keane L.', 'status' => 'Pending', 'doctor' => 'Dr. Bongon'],
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Bolanos, Ashely', 'status' => 'Pending', 'doctor' => 'Dr. Dacumos'],
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Yumul, Niccolo Franco S.', 'status' => 'Pending', 'doctor' => 'Dr. Nakaegawa'],
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Shellsea, Silvano', 'status' => 'Pending', 'doctor' => 'Dr. Nakaegawa'],
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Miralles, Peter Sloane', 'status' => 'Pending', 'doctor' => 'Dr. Nakaegawa'],
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Joaquin, Reina Jessamin', 'status' => 'Pending', 'doctor' => 'Dr. Dacumos'],
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Garcia, Jhemilyn', 'status' => 'Pending', 'doctor' => 'Dr. Dacumos'],
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Sialsa, June Rey', 'status' => 'Pending', 'doctor' => 'Dr. Bongon'],
  ['date' => 'May 4, 2026 8:00 PM', 'patient' => 'Busia, Gian Martin', 'status' => 'Pending', 'doctor' => 'Dr. Bongon'],
];

$doc_time_slots = [
  ['patient' => 'Abella, Gabriel Rey', 'time' => '8 am'],
  ['patient' => 'Ignacio, Keane', 'time' => '9 am'],
  ['patient' => 'Presto, Vin Hendrix', 'time' => '10 am'],
  ['patient' => 'Miralles, Peter', 'time' => '11 am'],
  ['patient' => 'Bolanos, Ashley', 'time' => '12 pm'],
  ['patient' => 'Yumul, Niccolo Franco', 'time' => '1 pm'],
];
