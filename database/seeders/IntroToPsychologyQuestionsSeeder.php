<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntroToPsychologyQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $teacherId = 1;
        $moduleId = 1;
        $level = 'L1';

        $questions = [
            // ─── HARD (10) ───────────────────────────────────────────────────────────
            [
                'difficulty' => 'hard',
                'text' => 'وفقاً لنظرية فرويد، أي من الآليات الدفاعية التالية تتضمن إعادة توجيه الطاقة النفسية من أهداف غير مقبولة اجتماعياً إلى أهداف مقبولة؟',
                'choices' => [
                    ['text' => 'الكبت', 'is_correct' => false, 'position' => 1],
                    ['text' => 'التسامي', 'is_correct' => true,  'position' => 2],
                    ['text' => 'الإسقاط', 'is_correct' => false, 'position' => 3],
                    ['text' => 'التحويل', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'hard',
                'text' => 'في نموذج مستويات المعالجة لكريك ولوكهارت (1972)، ما الذي يحدد قوة الذاكرة؟',
                'choices' => [
                    ['text' => 'مدة التكرار', 'is_correct' => false, 'position' => 1],
                    ['text' => 'عمق معالجة المعلومات', 'is_correct' => true,  'position' => 2],
                    ['text' => 'حجم الذاكرة العاملة', 'is_correct' => false, 'position' => 3],
                    ['text' => 'سرعة الاسترجاع', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'hard',
                'text' => 'أي من الهياكل الدماغية يلعب دوراً محورياً في ربط الاستجابات العاطفية بالذكريات؟',
                'choices' => [
                    ['text' => 'الحُصين (hippocampus)', 'is_correct' => false, 'position' => 1],
                    ['text' => 'اللوزة الدماغية (amygdala)', 'is_correct' => true,  'position' => 2],
                    ['text' => 'المخيخ (cerebellum)', 'is_correct' => false, 'position' => 3],
                    ['text' => 'الجسم الثفني (corpus callosum)', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'hard',
                'text' => 'وفقاً لنظرية بياجيه للنمو المعرفي، في أي مرحلة يطور الطفل مفهوم "ثبات الموضوع" (object permanence)؟',
                'choices' => [
                    ['text' => 'المرحلة الحسية الحركية', 'is_correct' => true,  'position' => 1],
                    ['text' => 'مرحلة ما قبل العمليات', 'is_correct' => false, 'position' => 2],
                    ['text' => 'مرحلة العمليات المادية', 'is_correct' => false, 'position' => 3],
                    ['text' => 'مرحلة العمليات الشكلية', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'hard',
                'text' => 'ما الفرق الرئيسي بين الارتباط والسببية في البحث النفسي؟',
                'choices' => [
                    ['text' => 'الارتباط أقوى من السببية دائماً', 'is_correct' => false, 'position' => 1],
                    ['text' => 'الارتباط يصف العلاقة بين متغيرين دون إثبات أن أحدهما يسبب الآخر', 'is_correct' => true,  'position' => 2],
                    ['text' => 'السببية تُستخدم في الدراسات الوصفية فقط', 'is_correct' => false, 'position' => 3],
                    ['text' => 'الارتباط يتطلب مجموعة ضابطة', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'hard',
                'text' => 'أي من مبادئ الغشطالت يشير إلى ميلنا لإدراك العناصر المتقاربة مكانياً كمجموعة واحدة؟',
                'choices' => [
                    ['text' => 'مبدأ التشابه', 'is_correct' => false, 'position' => 1],
                    ['text' => 'مبدأ الاستمرارية', 'is_correct' => false, 'position' => 2],
                    ['text' => 'مبدأ التقارب', 'is_correct' => true,  'position' => 3],
                    ['text' => 'مبدأ الإغلاق', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'hard',
                'text' => 'في نموذج أتكنسون وشيفرين للذاكرة، ما الوظيفة الرئيسية للذاكرة العاملة؟',
                'choices' => [
                    ['text' => 'تخزين المعلومات طويلة الأمد', 'is_correct' => false, 'position' => 1],
                    ['text' => 'معالجة المعلومات الواردة مؤقتاً وتشغيلها بنشاط', 'is_correct' => true,  'position' => 2],
                    ['text' => 'تصفية المثيرات الحسية الواردة', 'is_correct' => false, 'position' => 3],
                    ['text' => 'نقل المعلومات من الحواس إلى المخ مباشرة', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'hard',
                'text' => 'أي المفاهيم يصف ظاهرة الامتثال عندما يكون الطلب الأول صغيراً يعقبه طلب أكبر؟',
                'choices' => [
                    ['text' => 'تأثير الإذعان (door-in-the-face)', 'is_correct' => false, 'position' => 1],
                    ['text' => 'تقنية القدم في الباب (foot-in-the-door)', 'is_correct' => true,  'position' => 2],
                    ['text' => 'تأثير الهالة (halo effect)', 'is_correct' => false, 'position' => 3],
                    ['text' => 'الطاعة للسلطة', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'hard',
                'text' => 'ما المقصود بـ "العتبة المطلقة" (absolute threshold) في علم النفس الفيزيائي؟',
                'choices' => [
                    ['text' => 'الحد الأقصى للمثير الذي يمكن إدراكه', 'is_correct' => false, 'position' => 1],
                    ['text' => 'أدنى مستوى من التحفيز يمكن اكتشافه في 50% من الحالات', 'is_correct' => true,  'position' => 2],
                    ['text' => 'الفرق الأدنى الملحوظ بين مثيرين', 'is_correct' => false, 'position' => 3],
                    ['text' => 'قدرة الجهاز العصبي على تجاهل المثيرات المتكررة', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'hard',
                'text' => 'أي الشروط التالية ضرورية وفق DSM-5 لتشخيص الاضطراب النفسي؟',
                'choices' => [
                    ['text' => 'أن يكون السلوك غير مألوف في الثقافة السائدة', 'is_correct' => false, 'position' => 1],
                    ['text' => 'أن يسبب ضائقة أو إعاقة وظيفية ملحوظة', 'is_correct' => true,  'position' => 2],
                    ['text' => 'أن يستمر لأكثر من ستة أشهر في جميع الحالات', 'is_correct' => false, 'position' => 3],
                    ['text' => 'أن يتطلب دواءً نفسياً', 'is_correct' => false, 'position' => 4],
                ],
            ],

            // ─── MEDIUM (15) ─────────────────────────────────────────────────────────
            [
                'difficulty' => 'medium',
                'text' => 'من هو مؤسس أول مختبر تجريبي لعلم النفس عام 1879؟',
                'choices' => [
                    ['text' => 'سيغموند فرويد', 'is_correct' => false, 'position' => 1],
                    ['text' => 'وليام جيمس', 'is_correct' => false, 'position' => 2],
                    ['text' => 'فيلهلم فونت', 'is_correct' => true,  'position' => 3],
                    ['text' => 'إيفان بافلوف', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'ما الفرق بين الاشتراط الكلاسيكي والاشتراط الإجرائي؟',
                'choices' => [
                    ['text' => 'الاشتراط الكلاسيكي يعتمد على المكافأة والعقاب', 'is_correct' => false, 'position' => 1],
                    ['text' => 'الاشتراط الكلاسيكي يرتبط باستجابات لاإرادية، والإجرائي بسلوك إرادي', 'is_correct' => true,  'position' => 2],
                    ['text' => 'الاشتراط الإجرائي يعتمد على مثيرات محايدة', 'is_correct' => false, 'position' => 3],
                    ['text' => 'لا فرق جوهري بينهما', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'أي من التوجهات النظرية يرى أن السلوك الإنساني تحكمه قوى لاشعورية؟',
                'choices' => [
                    ['text' => 'السلوكية', 'is_correct' => false, 'position' => 1],
                    ['text' => 'المعرفية', 'is_correct' => false, 'position' => 2],
                    ['text' => 'التحليل النفسي', 'is_correct' => true,  'position' => 3],
                    ['text' => 'الإنسانية', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'في نظرية ماسلو للحاجات، أي مستوى يأتي مباشرة بعد الحاجات الفسيولوجية؟',
                'choices' => [
                    ['text' => 'حاجات الانتماء', 'is_correct' => false, 'position' => 1],
                    ['text' => 'حاجات الأمان', 'is_correct' => true,  'position' => 2],
                    ['text' => 'حاجات التقدير', 'is_correct' => false, 'position' => 3],
                    ['text' => 'تحقيق الذات', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'ما المقصود بـ "التعزيز الجزئي" في الاشتراط الإجرائي؟',
                'choices' => [
                    ['text' => 'تعزيز جميع الاستجابات دون استثناء', 'is_correct' => false, 'position' => 1],
                    ['text' => 'تعزيز الاستجابة في بعض الأحيان وليس في كل مرة', 'is_correct' => true,  'position' => 2],
                    ['text' => 'تقليل شدة المعزز تدريجياً', 'is_correct' => false, 'position' => 3],
                    ['text' => 'استخدام معززين في نفس الوقت', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'أي من الناقلات العصبية يرتبط بالمزاج والاكتئاب بشكل رئيسي؟',
                'choices' => [
                    ['text' => 'الدوبامين', 'is_correct' => false, 'position' => 1],
                    ['text' => 'الأسيتيل كولين', 'is_correct' => false, 'position' => 2],
                    ['text' => 'السيروتونين', 'is_correct' => true,  'position' => 3],
                    ['text' => 'الغلوتامات', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'ما الفرق بين المتغير المستقل والمتغير التابع في التجربة النفسية؟',
                'choices' => [
                    ['text' => 'المتغير المستقل هو الذي يقيسه الباحث', 'is_correct' => false, 'position' => 1],
                    ['text' => 'المتغير المستقل يتحكم فيه الباحث، والتابع هو الذي يُقاس', 'is_correct' => true,  'position' => 2],
                    ['text' => 'كلاهما يُقاس بنفس الطريقة', 'is_correct' => false, 'position' => 3],
                    ['text' => 'المتغير التابع يؤثر في المستقل', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'أي من أساليب قياس النشاط الدماغي يرصد الإشارات الكهربائية مباشرة؟',
                'choices' => [
                    ['text' => 'التصوير بالرنين المغناطيسي الوظيفي (fMRI)', 'is_correct' => false, 'position' => 1],
                    ['text' => 'تخطيط الدماغ الكهربائي (EEG)', 'is_correct' => true,  'position' => 2],
                    ['text' => 'التصوير المقطعي بالإصدار البوزيتروني (PET)', 'is_correct' => false, 'position' => 3],
                    ['text' => 'التصوير بالرنين المغناطيسي التشريحي (MRI)', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'أي منهج بحثي يسمح للباحث بإثبات العلاقة السببية بين المتغيرات؟',
                'choices' => [
                    ['text' => 'الدراسة الارتباطية', 'is_correct' => false, 'position' => 1],
                    ['text' => 'دراسة الحالة', 'is_correct' => false, 'position' => 2],
                    ['text' => 'التجربة المضبوطة', 'is_correct' => true,  'position' => 3],
                    ['text' => 'الملاحظة الطبيعية', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'ما التعريف الصحيح للوعي (consciousness) في علم النفس؟',
                'choices' => [
                    ['text' => 'الحالة الدائمة للانتباه الكامل', 'is_correct' => false, 'position' => 1],
                    ['text' => 'الوعي بالذات والبيئة في لحظة معينة', 'is_correct' => true,  'position' => 2],
                    ['text' => 'القدرة على التذكر والتخيل فقط', 'is_correct' => false, 'position' => 3],
                    ['text' => 'النشاط الكهربائي في القشرة الدماغية', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'ما المقصود بـ "الانتقاء الطبيعي" في علم النفس التطوري؟',
                'choices' => [
                    ['text' => 'قدرة الفرد على اختيار بيئته', 'is_correct' => false, 'position' => 1],
                    ['text' => 'العملية التي تبقى فيها الصفات المساعدة على البقاء وتنتقل للأجيال', 'is_correct' => true,  'position' => 2],
                    ['text' => 'التعلم الاجتماعي عبر الأجيال', 'is_correct' => false, 'position' => 3],
                    ['text' => 'اختيار الشريك الأنسب', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'أي من الاضطرابات التالية تندرج ضمن اضطرابات القلق وفق DSM-5؟',
                'choices' => [
                    ['text' => 'الاضطراب ثنائي القطب', 'is_correct' => false, 'position' => 1],
                    ['text' => 'الفصام', 'is_correct' => false, 'position' => 2],
                    ['text' => 'اضطراب الهلع', 'is_correct' => true,  'position' => 3],
                    ['text' => 'الاكتئاب الشديد', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'ما مفهوم "الذاتية المتشاركة" (intersubjectivity) في علم النفس؟',
                'choices' => [
                    ['text' => 'قدرة الفرد على الانفصال التام عن الآخرين', 'is_correct' => false, 'position' => 1],
                    ['text' => 'التفاهم والمعنى المشترك بين الأفراد', 'is_correct' => true,  'position' => 2],
                    ['text' => 'التحيز الذاتي في الحكم على الآخرين', 'is_correct' => false, 'position' => 3],
                    ['text' => 'اختلاف الإدراك الحسي بين الأفراد', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'ما المقصود بـ "الإسقاط" (projection) كآلية دفاعية عند فرويد؟',
                'choices' => [
                    ['text' => 'تحويل القلق إلى أعراض جسدية', 'is_correct' => false, 'position' => 1],
                    ['text' => 'نسب مشاعر أو رغبات الفرد غير المقبولة إلى شخص آخر', 'is_correct' => true,  'position' => 2],
                    ['text' => 'العودة إلى سلوك مرحلة نمائية أبكر', 'is_correct' => false, 'position' => 3],
                    ['text' => 'إعادة توجيه الطاقة إلى هدف مقبول', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'medium',
                'text' => 'ما الغرض من "المجموعة الضابطة" (control group) في التجربة النفسية؟',
                'choices' => [
                    ['text' => 'تلقّي المعالجة التجريبية الرئيسية', 'is_correct' => false, 'position' => 1],
                    ['text' => 'توفير خط أساس للمقارنة مع المجموعة التجريبية', 'is_correct' => true,  'position' => 2],
                    ['text' => 'قياس المتغير المستقل فقط', 'is_correct' => false, 'position' => 3],
                    ['text' => 'تحديد حجم العينة المطلوب', 'is_correct' => false, 'position' => 4],
                ],
            ],

            // ─── EASY (15) ───────────────────────────────────────────────────────────
            [
                'difficulty' => 'easy',
                'text' => 'ما تعريف علم النفس؟',
                'choices' => [
                    ['text' => 'دراسة الدماغ والجهاز العصبي فقط', 'is_correct' => false, 'position' => 1],
                    ['text' => 'الدراسة العلمية للسلوك والعمليات العقلية', 'is_correct' => true,  'position' => 2],
                    ['text' => 'دراسة الاضطرابات النفسية فقط', 'is_correct' => false, 'position' => 3],
                    ['text' => 'الفلسفة الحديثة للعقل', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'من هو مؤسس علم التحليل النفسي؟',
                'choices' => [
                    ['text' => 'كارل يونغ', 'is_correct' => false, 'position' => 1],
                    ['text' => 'آلفريد أدلر', 'is_correct' => false, 'position' => 2],
                    ['text' => 'سيغموند فرويد', 'is_correct' => true,  'position' => 3],
                    ['text' => 'وليام جيمس', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما المقصود بـ "السلوك" في علم النفس؟',
                'choices' => [
                    ['text' => 'الأفكار والمشاعر الداخلية فقط', 'is_correct' => false, 'position' => 1],
                    ['text' => 'كل ما يمكن ملاحظته وقياسه من أفعال الكائن الحي', 'is_correct' => true,  'position' => 2],
                    ['text' => 'ردود الفعل اللاإرادية فقط', 'is_correct' => false, 'position' => 3],
                    ['text' => 'النشاط الدماغي الكهربائي', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما المقصود بـ "الدافعية" في علم النفس؟',
                'choices' => [
                    ['text' => 'الاستجابة الانعكاسية للمثيرات', 'is_correct' => false, 'position' => 1],
                    ['text' => 'القوى الداخلية أو الخارجية التي تحرّك السلوك وتوجهه نحو هدف', 'is_correct' => true,  'position' => 2],
                    ['text' => 'القدرة على حل المشكلات', 'is_correct' => false, 'position' => 3],
                    ['text' => 'الشعور بالارتياح بعد الإنجاز', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما الفرق بين علم النفس العيادي وعلم النفس العام؟',
                'choices' => [
                    ['text' => 'علم النفس العيادي يدرس الحيوانات', 'is_correct' => false, 'position' => 1],
                    ['text' => 'علم النفس العيادي يركز على تشخيص وعلاج الاضطرابات النفسية', 'is_correct' => true,  'position' => 2],
                    ['text' => 'علم النفس العيادي نظري بحت', 'is_correct' => false, 'position' => 3],
                    ['text' => 'لا فرق بينهما', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما المقصود بالتعلم في علم النفس؟',
                'choices' => [
                    ['text' => 'النضج البيولوجي الطبيعي', 'is_correct' => false, 'position' => 1],
                    ['text' => 'تغيير شبه دائم في السلوك أو المعرفة ناتج عن الخبرة', 'is_correct' => true,  'position' => 2],
                    ['text' => 'حفظ المعلومات في الذاكرة قصيرة المدى', 'is_correct' => false, 'position' => 3],
                    ['text' => 'الاستجابة الغريزية للمثيرات', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما معنى مصطلح "اللاشعور" (unconscious) عند فرويد؟',
                'choices' => [
                    ['text' => 'حالة النوم العميق', 'is_correct' => false, 'position' => 1],
                    ['text' => 'الجزء من العقل الذي يحتوي على أفكار ورغبات لا يدركها الفرد', 'is_correct' => true,  'position' => 2],
                    ['text' => 'الذاكرة قصيرة المدى', 'is_correct' => false, 'position' => 3],
                    ['text' => 'الحالة التي يكون فيها الإنسان غير منتبه', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما المقصود بـ "الإدراك الحسي" (perception)؟',
                'choices' => [
                    ['text' => 'استقبال المثيرات الحسية من البيئة فقط', 'is_correct' => false, 'position' => 1],
                    ['text' => 'عملية تنظيم وتفسير المعلومات الحسية لفهم البيئة', 'is_correct' => true,  'position' => 2],
                    ['text' => 'الاستجابة الآلية للمثيرات', 'is_correct' => false, 'position' => 3],
                    ['text' => 'نقل الإشارات العصبية إلى الدماغ فقط', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'من صاحب مفهوم "مجمع النقص" (inferiority complex)؟',
                'choices' => [
                    ['text' => 'سيغموند فرويد', 'is_correct' => false, 'position' => 1],
                    ['text' => 'كارل يونغ', 'is_correct' => false, 'position' => 2],
                    ['text' => 'آلفريد أدلر', 'is_correct' => true,  'position' => 3],
                    ['text' => 'إريك إريكسون', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما المقصود بـ "الانعكاس" (reflex)؟',
                'choices' => [
                    ['text' => 'سلوك تعلّمه الفرد', 'is_correct' => false, 'position' => 1],
                    ['text' => 'استجابة تلقائية وسريعة لمثير معين دون تدخل واعٍ', 'is_correct' => true,  'position' => 2],
                    ['text' => 'ردّ فعل عاطفي قوي', 'is_correct' => false, 'position' => 3],
                    ['text' => 'عملية اتخاذ قرار سريع', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما الغرض الرئيسي من الفرضية في البحث النفسي؟',
                'choices' => [
                    ['text' => 'تلخيص نتائج البحث', 'is_correct' => false, 'position' => 1],
                    ['text' => 'وصف المشاركين في الدراسة', 'is_correct' => false, 'position' => 2],
                    ['text' => 'تقديم تنبؤ قابل للاختبار حول نتيجة متوقعة', 'is_correct' => true,  'position' => 3],
                    ['text' => 'تحديد أسلوب جمع البيانات', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما الجهاز العصبي المسؤول عن استجابة "الكر أو الفر" (fight or flight)؟',
                'choices' => [
                    ['text' => 'الجهاز العصبي المركزي', 'is_correct' => false, 'position' => 1],
                    ['text' => 'الجهاز العصبي السمبثاوي', 'is_correct' => true,  'position' => 2],
                    ['text' => 'الجهاز العصبي الباراسمبثاوي', 'is_correct' => false, 'position' => 3],
                    ['text' => 'الجهاز العصبي الجسدي', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما هدف الملاحظة الطبيعية (naturalistic observation) كمنهج بحثي؟',
                'choices' => [
                    ['text' => 'التحكم في المتغيرات لإثبات السببية', 'is_correct' => false, 'position' => 1],
                    ['text' => 'مراقبة السلوك في بيئته الطبيعية دون تدخل', 'is_correct' => true,  'position' => 2],
                    ['text' => 'إجراء مقابلات عميقة مع المشاركين', 'is_correct' => false, 'position' => 3],
                    ['text' => 'تطبيق استبيانات على عينات كبيرة', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'أي من التخصصات التالية يندرج ضمن فروع علم النفس التطبيقي؟',
                'choices' => [
                    ['text' => 'علم النفس التجريبي', 'is_correct' => false, 'position' => 1],
                    ['text' => 'علم النفس الفسيولوجي', 'is_correct' => false, 'position' => 2],
                    ['text' => 'علم النفس الصناعي والتنظيمي', 'is_correct' => true,  'position' => 3],
                    ['text' => 'علم النفس المقارن', 'is_correct' => false, 'position' => 4],
                ],
            ],
            [
                'difficulty' => 'easy',
                'text' => 'ما الفرق بين الذاكرة قصيرة المدى والذاكرة طويلة المدى؟',
                'choices' => [
                    ['text' => 'الذاكرة قصيرة المدى تخزن معلومات دائمة بينما الطويلة مؤقتة', 'is_correct' => false, 'position' => 1],
                    ['text' => 'الذاكرة قصيرة المدى محدودة السعة والمدة، والطويلة واسعة السعة ودائمة نسبياً', 'is_correct' => true,  'position' => 2],
                    ['text' => 'لا فرق بينهما من حيث السعة', 'is_correct' => false, 'position' => 3],
                    ['text' => 'الذاكرة الطويلة المدى تعالج المعلومات بشكل أبطأ دائماً', 'is_correct' => false, 'position' => 4],
                ],
            ],
        ];

        foreach ($questions as $q) {
            $questionId = DB::table('questions')->insertGetId([
                'teacher_id' => $teacherId,
                'module_id'  => $moduleId,
                'level'      => $level,
                'difficulty' => $q['difficulty'],
                'text'       => $q['text'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($q['choices'] as $choice) {
                DB::table('question_choices')->insert([
                    'question_id' => $questionId,
                    'text'        => $choice['text'],
                    'is_correct'  => $choice['is_correct'],
                    'position'    => $choice['position'],
                ]);
            }
        }
    }
}
