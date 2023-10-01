<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\View\JsonView;
use App\Model\Table\AddressesTable;
use App\Services\ViaCep;

/**
 * Stores Controller
 *
 * @property \App\Model\Table\StoresTable $Stores
 * @method \App\Model\Entity\Store[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class StoresController extends AppController
{
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $store = $this->Stores->find('all')->contain(['Addresses'])->all();

        $this->set(compact('store'));
    }

    /**
     * View method
     *
     * @param string|null $id Store id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $store = $this->Stores->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('store'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $data = $this->request->getData();

        $store = $this->Stores->newEmptyEntity();
        if ($this->request->is('post')) {
            $store = $this->Stores->patchEntity($store, [
                "name" => $data["name"]
            ]);

            $address = null;

            
            if ($this->Stores->save($store)) {
                $this->Flash->success(__('The store has been saved.'));

                $addressTable = new AddressesTable();

                $address = $addressTable->newEmptyEntity();

                

                $serviceViaCep = new ViaCep();

                $cep = json_decode($serviceViaCep->findCep($data['postal_code']));

                $address = $addressTable->patchEntity($address, [                    
                    "postal_code" => $cep->cep,
                    "sublocality" => "US",
                    "street_number" => "123",
                    "complement" => "123",
                    "street" => $cep->logradouro,
                    "city" => "city",
                    "state" => "MG",                
                    "foreign_id" => $store->id,
                    "foreign_table" => "stores",
                ]);

                

                if ($address->hasErrors()) {
                    $this->set('errors', $address->getErrors());
                }
                $addressTable->save($address);

                // return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The store could not be saved. Please, try again.'));
        }
        $this->set(compact('store', 'address', 'cep'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Store id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $store = $this->Stores->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $store = $this->Stores->patchEntity($store, $this->request->getData());
            if ($this->Stores->save($store)) {
                $this->Flash->success(__('The store has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The store could not be saved. Please, try again.'));
        }
        $this->set(compact('store'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Store id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $store = $this->Stores->get($id);
        if ($this->Stores->delete($store)) {
            $this->Flash->success(__('The store has been deleted.'));
        } else {
            $this->Flash->error(__('The store could not be deleted. Please, try again.'));
        }

        // return $this->redirect(['action' => 'index']);
    }
}
