<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Support\Answer\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Support\Answer\Entity\SupportAnswer;
use BaksDev\Support\Answer\UseCase\Admin\NewEdit\SupportAnswerDTO;
use BaksDev\Support\Answer\UseCase\Admin\NewEdit\SupportAnswerForm;
use BaksDev\Support\Answer\UseCase\Admin\NewEdit\SupportAnswerHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_SUPPORT_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/support/answer/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        #[MapEntity] SupportAnswer $SupportAnswer,
        Request $request,
        SupportAnswerHandler $supportAnswerHandler,
    ): Response
    {

        $SupportAnswerDTO = new SupportAnswerDTO()
            ->setId($SupportAnswer->getId())
            ->setTitle($SupportAnswer->getTitle())
            ->setType($SupportAnswer->getType())
            ->setContent($SupportAnswer->getContent());

        /** Форма */
        $form = $this
            ->createForm(
                type: SupportAnswerForm::class,
                data: $SupportAnswerDTO,
                options: ['action' => $this->generateUrl(
                    route: 'support-answer:admin.newedit.edit',
                    parameters: ['id' => $SupportAnswer->getId()]
                ),]
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->refreshTokenForm($form);

            $handle = $supportAnswerHandler->handle($SupportAnswerDTO);

            $this->addFlash(
                'page.edit',
                $handle instanceof SupportAnswer ? 'success.edit' : 'danger.edit',
                'support-answer.admin',
                $handle
            );

            return $this->redirectToRoute('support-answer:admin.index');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
