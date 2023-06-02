<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Reponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\QuestionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\Form\ReponseType;

class IndexController extends AbstractController
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @Route("/", name="question_list", methods={"GET"})
     */
    public function home(): Response
    {
        $questions = $this->registry->getManager()->getRepository(Question::class)->findAll();

        return $this->render('question/index.html.twig', ['questions' => $questions]);
    }

    /**
     * @Route("/api/questions/save", name="save_question", methods={"POST"})
     */
    public function save(): Response
    {
        $entityManager = $this->registry->getManager();
        $question = new Question();
        $question->setTitre('question 3');
        $question->setContenu('question');

        $entityManager->persist($question);
        $entityManager->flush();

        return new Response('Question enregistrée avec ID '.$question->getId());
    }

    /**
     * @Route("/question/new", name="new_question", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->registry->getManager();
            $entityManager->persist($question);
            $entityManager->flush();

            return $this->redirectToRoute('question_list');
        }

        return $this->render('question/new.html.twig', ['form' => $form->createView()]);
    }

        /**
         * @Route("/question/{id}", name="question_show", methods={"GET", "POST"})
         */
        public function show(Request $request, $id, ManagerRegistry $registry)
    {
        $question = $registry->getRepository(Question::class)->find($id);
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setQuestion($question);

            $entityManager = $registry->getManager();
            $entityManager->persist($reponse);
            $entityManager->flush();

            // Redirect or add flash message if needed
        }
        return $this->render('question/show.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("question/edit/{id}", name="edit_question", methods={"GET", "POST"})
     */
    public function edit(Request $request, $id): Response
    {
        $entityManager = $this->registry->getManager();
        $question = $entityManager->getRepository(Question::class)->find($id);
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('question_list');
        }

        return $this->render('question/edit.html.twig', ['form' => $form->createView()]);
    }

        /**
     * @Route("/question/delete/{id}", name="delete_question", methods={"DELETE" , "GET"})
     */
    public function delete(Request $request, $id, ManagerRegistry $registry): Response
    {
        $entityManager = $registry->getManager();
        $question = $entityManager->getRepository(Question::class)->find($id);

        // Check if the question exists
        if (!$question) {
            throw $this->createNotFoundException('Question not found');
        }

        // Retrieve and delete the associated reponses
        $reponses = $question->getReponses();
        foreach ($reponses as $reponse) {
            $entityManager->remove($reponse);
        }

        $entityManager->remove($question);
        $entityManager->flush();

        return $this->redirectToRoute('question_list');
    }


    /**
     * @Route("/question/{id}/reponse/newReponse", name="new_reponse", methods={"POST", "GET"})
     */
    public function newReponse(Request $request): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->registry->getManager();
            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('question_list');
        }

        return $this->render('reponses/newReponse.html.twig', [
            'form' => $form->createView(),
        ]);
    }



}
