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

declare(strict_types=1);

namespace BaksDev\Support\Answer\UseCase\Admin\NewEdit;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Support\Answer\Entity\SupportAnswer;
use BaksDev\Support\Answer\Messenger\SupportAnswerMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class SupportAnswerHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageDispatchInterface $messageDispatch,
        private ValidatorInterface $validator
    ) {}

    public function handle(SupportAnswerDTO $command): bool|string|SupportAnswer
    {
        $errors = $this->validator->validate($command);

        if($errors->count())
        {
            return false;
        }

        $SupportAnswer = $command->getId() ?
            $this->entityManager->getRepository(SupportAnswer::class)->find($command->getId()) :
            new SupportAnswer();
        
        if(false === ($SupportAnswer instanceof SupportAnswer))
        {
            return false;
        }

        $SupportAnswer->setTitle($command->getTitle())
            ->setContent($command->getContent())
            ->setType($command->getType())
        ;
        $this->entityManager->persist($SupportAnswer);
        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $message = new SupportAnswerMessage($command->getId());

        $this->messageDispatch->dispatch(
            message: $message,
            transport: 'support-answer'
        );

        return $SupportAnswer;
    }
}