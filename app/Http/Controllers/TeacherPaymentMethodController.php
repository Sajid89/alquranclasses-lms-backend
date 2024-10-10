<?php

namespace App\Http\Controllers;

use App\Helpers\GeneralHelper;
use App\Http\Requests\TeacherPaymentMethodRequest;
use App\Jobs\SendEmailOnPaymentMethodSubmit;
use App\Models\Notification;
use App\Repository\TeacherPaymentMethodRepository;
use Illuminate\Http\Request;


class TeacherPaymentMethodController extends Controller
{
    private $teacherPaymentMethodRequest;
    private $teacherPaymentMethodRepository;

    public function __construct(
        TeacherPaymentMethodRequest $teacherPaymentMethodRequest, 
        TeacherPaymentMethodRepository $teacherPaymentMethodRepository)
    {
        $this->teacherPaymentMethodRequest = $teacherPaymentMethodRequest;
        $this->teacherPaymentMethodRepository = $teacherPaymentMethodRepository;
    }

    /**
     * Create teacher payment method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $this->teacherPaymentMethodRequest->validateCreate(request());

        $data = $request->all();
        $data['user_id'] = auth()->user()->id;

        $idCardImages = $request->file('attachments');
        $keys = ['id_card_front_img', 'id_card_back_img'];

        foreach ($idCardImages as $index => $cardImage) {
            $path = 'images/teacher_payment_methods';
            $data[$keys[$index]] = GeneralHelper::uploadProfileImage($cardImage, $path);
        }

        $this->teacherPaymentMethodRepository->create($data);

        // Send email to teacher
        $emailData = [
            'name' => auth()->user()->name,
            'email' => auth()->user()->email
        ];
        dispatch(new SendEmailOnPaymentMethodSubmit($emailData));

        // Notify teacher
        Notification::create([
            'user_id' => $data['user_id'],
            'type' => 'payment_method',
            'read' => false,
            'message' => "You request for new bank account information has been sent for review."
        ]);

        return $this->success($data, 'Teacher payment method added successfully', 201);
    }

    /**
     * Update teacher payment method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->teacherPaymentMethodRequest->validateUpdate(request());

        $teacherId = auth()->user()->id;
        $paymentMethod = $this->teacherPaymentMethodRepository->findByUserId($teacherId);

        if (!$paymentMethod) {
            return $this->error('Teacher payment method not found', 404);
        }

        if ($paymentMethod->is_locked) {
            return $this->error('Teacher payment method is locked', 403);
        }

        $data = $request->all();
        $data['is_approved'] = 0;
        $data['is_locked'] = 1;

        if ($request->hasFile('attachments')) {
            $idCardImages = $request->file('attachments');
            $imageTypes = ['id_card_front_img', 'id_card_back_img'];
        
            foreach ($idCardImages as $index => $image) {
                if ($image !== null) {
                    // Determine the type of image (front or back) based on the index
                    $imageType = $imageTypes[$index];
                    $oldImagePath = $paymentMethod->$imageType;
        
                    // Delete the old image if it exists
                    if ($oldImagePath) {
                        GeneralHelper::deleteImage($oldImagePath);
                    }
        
                    // Upload the new image and update the path in the data array
                    $path = 'images/teacher_payment_methods';
                    $data[$imageType] = GeneralHelper::uploadProfileImage($image, $path);
                }
            }
        }

        $this->teacherPaymentMethodRepository->update($paymentMethod->id, $data);

        // Send email to teacher
        $emailData = [
            'name' => auth()->user()->name,
            'email' => auth()->user()->email
        ];
        dispatch(new SendEmailOnPaymentMethodSubmit($emailData));

        // Notify teacher
        Notification::create([
            'user_id' => $teacherId,
            'type' => 'payment_method',
            'read' => false,
            'message' => "You request for new bank account information has been sent for review."
        ]);

        return $this->success($request->all(), 'Teacher payment method updated successfully', 200);
    }

    /**
     * Get teacher payment method
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentMethod()
    {
        $teacherId = auth()->user()->id;

        $paymentMethod = $this->teacherPaymentMethodRepository->findByUserId($teacherId);

        return $this->success($paymentMethod, 'Teacher payment method found', 200);
    }
}