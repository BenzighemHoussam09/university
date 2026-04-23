<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'الرياضيات', 'الفيزياء', 'الكيمياء', 'علم الأحياء', 'علوم الحاسوب',
            'الخوارزميات وهياكل البيانات', 'أنظمة التشغيل', 'قواعد البيانات',
            'هندسة البرمجيات', 'الشبكات والبروتوكولات', 'تقنيات الويب',
            'الذكاء الاصطناعي', 'تعلم الآلة', 'معالجة الإشارات',
            'الإلكترونيك', 'الكهروتقنية', 'الديناميكا الحرارية', 'الميكانيكا',
            'الإحصاء والاحتمالات', 'الجبر الخطي', 'التحليل الرياضي',
            'اللغة العربية', 'اللغة الفرنسية', 'اللغة الإنجليزية',
            'الإدارة والاقتصاد', 'القانون والتشريعات', 'الفلسفة',
            'تاريخ العلوم', 'الرسم التقني', 'إدارة المشاريع',
        ];

        foreach ($modules as $name) {
            DB::table('module_catalog')->updateOrInsert(
                ['name' => $name],
                ['name' => $name, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
