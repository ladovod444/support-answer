<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Support\Answer\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Support\Answer\Entity\SupportAnswer;
use BaksDev\Support\Answer\UseCase\Admin\Delete\SupportAnswerDeleteDTO;
use BaksDev\Support\Answer\UseCase\Admin\Delete\SupportAnswerDeleteForm;
use BaksDev\Support\Answer\UseCase\Admin\Delete\SupportAnswerDeleteHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_SUPPORT_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/support/answer/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] SupportAnswer $SupportAnswer,
        SupportAnswerDeleteHandler $SupportAnswerDeleteHandler,
    ): Response
    {

        $SupportAnswerDeleteDTO = new SupportAnswerDeleteDTO();
        $SupportAnswerDeleteDTO->setId($SupportAnswer->getId());

        $form = $this
            ->createForm(SupportAnswerDeleteForm::class, $SupportAnswerDeleteDTO, [
                'action' => $this->generateUrl(
                    'support-answer:admin.delete',
                    ['id' => $SupportAnswer->getId()]
                ),
            ])
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->refreshTokenForm($form);

            $handle = $SupportAnswerDeleteHandler->handle($SupportAnswerDeleteDTO);

            $this->addFlash(
                'page.delete',
                $handle instanceof SupportAnswer ? 'success.delete' : 'danger.delete',
                'support-answer.admin',
                $handle
            );

            return $handle instanceof SupportAnswer ? $this->redirectToRoute('support-answer:admin.index') : $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView(),]);
    }
}