<?php

namespace App\Controller;

use App\Entity\Mesa;
use App\Entity\Parametro;
use App\Entity\Sindicato;
use App\Entity\Voto;
use App\Form\VotosSindicalesType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;

class EleccionesController extends AbstractController
{
    #[Route('/elecciones', name: 'app_elecciones')]
    public function index(EntityManagerInterface $em, Request $request, PaginatorInterface $paginator): Response
    {

        $mesas = $em->getRepository(Mesa::class)->findAll();

        $query = $em->createQuery('SELECT p FROM App\Entity\Mesa p');

        // Paginar los resultados de la consulta
        $pagination = $paginator->paginate( 
            // Consulta Doctrine, no resultados
            $query,
            // Definir el parámetro de la página
            $request->query->getInt('page', 1),
            // Items per page
            3
        );

        return $this->render('elecciones/mesas.html.twig', [
            'mesas' => $mesas,
            'pagination' => $pagination
        ]);
    }

    #[Route('/elecciones/mesas_datos/{id?0}', name: 'app_mesas_datos')]
    public function mesasDatos(EntityManagerInterface $em, $id, Request $request): Response
    {
        $mesas = $em->getRepository(Mesa::class)->findOneBy(['id' => $id]);
        $votos = $em->getRepository(Voto::class)->findAll();
        $sindicatos = $em->getRepository(Sindicato::class)->findAll();

        $test= [];
        foreach ($votos as $key => $value) {
            if($value->getMesa()->getId() == $id) {
                $test[$key] = $value;
            }
        }

        $form = $this->createForm(VotosSindicalesType::class, null, [
            'votos' => $test,
            'sindicatos' => $sindicatos,
            'mesa_id' => $id
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            foreach ($votos as $key => $value) {
                if (isset($data['sindicato_' . $key])) {
                    $value->setVotos($data["sindicato_" . $key]);
                }

                $em->persist($value);
                $em->flush();
            }

            return $this->redirectToRoute('app_elecciones');
        }

        return $this->render('elecciones/mesas_datos.html.twig', [
            'mesa' => $mesas,
            'votos' => $votos,
            'form' => $form
        ]);
    }

    #[Route('/elecciones/resultados', name: 'app_resultados')]
    public function resultados(EntityManagerInterface $em, Request $request, PaginatorInterface $paginator): Response
    {

        $mesas = $em->getRepository(Mesa::class)->findAll();

        $votos = $em->getRepository(Voto::class)->findAll();
        $ratio = $em->getRepository(Parametro::class)->findOneBy(['id' => 1]);

        

        $votos_total = 0;

        // SUMAMOS LA CANTIDAD DE VOTOS DE CADA SINDICATO
        foreach ($votos as $key => $value) {
            $votos_total += $value->getVotos();
            $test[$key] = $value;


        }

        // Dividimos la suma de todos los votos entre el ratio
        $resultado_ratio = intdiv($votos_total, $ratio->getValor());


        dump("RATIO: " . $resultado_ratio);
        // $resultado_ratio = $votos_total / $ratio->getValor();

        foreach ($test as $key => $value) {
            // Dividimos los votos de cada sindicato entre  el resultado del ratio

            if($key == 0)
                $test[$key] = intdiv($value->getVotos(), $resultado_ratio) + 1;
            else
                $test[$key] = intdiv($value->getVotos(), $resultado_ratio);

            dump($test);
        }




        return $this->render('elecciones/resultados.html.twig', [
            'votos' => $votos,
            'test' => $test,
            'votos_total' => $votos_total
        ]);
    }
}
