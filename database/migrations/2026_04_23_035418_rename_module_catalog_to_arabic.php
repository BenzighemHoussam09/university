<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $map = [
        'Mathematics'                => 'الرياضيات',
        'Physics'                    => 'الفيزياء',
        'Chemistry'                  => 'الكيمياء',
        'Biology'                    => 'علم الأحياء',
        'Computer Science'           => 'علوم الحاسوب',
        'Algorithms & Data Structures' => 'الخوارزميات وهياكل البيانات',
        'Operating Systems'          => 'أنظمة التشغيل',
        'Database Systems'           => 'قواعد البيانات',
        'Software Engineering'       => 'هندسة البرمجيات',
        'Networks & Protocols'       => 'الشبكات والبروتوكولات',
        'Web Technologies'           => 'تقنيات الويب',
        'Artificial Intelligence'    => 'الذكاء الاصطناعي',
        'Machine Learning'           => 'تعلم الآلة',
        'Signal Processing'          => 'معالجة الإشارات',
        'Electronics'                => 'الإلكترونيك',
        'Electrotechnics'            => 'الكهروتقنية',
        'Thermodynamics'             => 'الديناميكا الحرارية',
        'Mechanics'                  => 'الميكانيكا',
        'Statistics & Probability'   => 'الإحصاء والاحتمالات',
        'Linear Algebra'             => 'الجبر الخطي',
        'Analysis'                   => 'التحليل الرياضي',
        'Arabic Language'            => 'اللغة العربية',
        'French Language'            => 'اللغة الفرنسية',
        'English Language'           => 'اللغة الإنجليزية',
        'Management & Economics'     => 'الإدارة والاقتصاد',
        'Law & Regulations'          => 'القانون والتشريعات',
        'Philosophy'                 => 'الفلسفة',
        'History of Sciences'        => 'تاريخ العلوم',
        'Technical Drawing'          => 'الرسم التقني',
        'Project Management'         => 'إدارة المشاريع',
    ];

    public function up(): void
    {
        foreach ($this->map as $en => $ar) {
            DB::table('module_catalog')->where('name', $en)->update(['name' => $ar]);
        }
    }

    public function down(): void
    {
        foreach ($this->map as $en => $ar) {
            DB::table('module_catalog')->where('name', $ar)->update(['name' => $en]);
        }
    }
};
